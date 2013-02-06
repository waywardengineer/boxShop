import serial
import urllib
import sys
import Skype4Py
import MySQLdb
import re
import calendar
import time
import wx
ID_START = wx.NewId()

			
class Frame(wx.Frame):
	def __init__(self, title):
		wx.Frame.__init__(self, None, title=title, size=(850,300))
		panel = wx.Panel(self)
		box = wx.BoxSizer(wx.VERTICAL)
		m_text = wx.StaticText(panel, -1, "Box Shop Alarm Control")
		m_text.SetFont(wx.Font(14, wx.SWISS, wx.NORMAL, wx.BOLD))
		m_text.SetSize(m_text.GetBestSize())
		box.Add(m_text, 0, wx.ALL, 10)

		self.m_status = wx.TextCtrl(panel, style=wx.TE_MULTILINE, pos=(100, 50), size=(700, 150))
		self.m_open = wx.Button(panel, ID_START, "Start")
		self.m_close = wx.Button(panel, wx.ID_CLOSE, "Stop")
		self.m_buzz = wx.Button(panel, wx.ID_CLOSE, "Buzz")
		self.m_alarm = wx.Button(panel, wx.ID_CLOSE, "Alarm")
		self.m_close.Bind(wx.EVT_BUTTON, self.OnClose)
		self.m_open.Bind(wx.EVT_BUTTON, self.OnStart)
		self.m_buzz.Bind(wx.EVT_BUTTON, self.OnBuzz)
		self.m_alarm.Bind(wx.EVT_BUTTON, self.OnAlarm)
		box.Add(self.m_open, 0, wx.ALL, 10)
		box.Add(self.m_close, 0, wx.ALL, 10)
		box.Add(self.m_buzz, 0, wx.ALL, 10)
		box.Add(self.m_alarm, 0, wx.ALL, 10)
		
		panel.SetSizer(box)
		panel.Layout()
		self.m_open.Disable()
		self.m_close.Enable()

					
	def OnClose(self, event):
		self.RUN_SERIAL = 0
		self.m_buzz.Disable()
		self.m_alarm.Disable()
		self.m_close.Disable()
		self.m_open.Enable()
		
	def OnStart(self, event):
		self.m_open.Disable()
		self.m_close.Enable()
		self.m_alarm.Enable()
		self.m_buzz.Enable()
		self.handleSerial()

	def OnBuzz(self, event):
		self.btnEvent = 1
	def mysqlExecute(self, query):
		connectTries = 0
		connected = False
		result = False
		while (not connected) and connectTries < 2:
			try:
				self.dbc.execute('SELECT ID FROM codes WHERE 1 LIMIT 1')
				connected = True
			except Exception as inst:
				self.m_status.AppendText('attempting reconnection to database\n')
				self.mysqlConnect()
				time.sleep(1)
				connectTries += 1
		if connected and query:
			try:
				self.dbc.execute(query)

				result = True
			except Exception as inst:
				self.m_status.AppendText('failed executing query\n')
				self.m_status.AppendText(str(query) + '\n')
		return result			
		
				

			
			
	def OnAlarm(self, event):
		self.btnEvent = 2
	def mysqlConnect(self):
		try:
			self.db=MySQLdb.connect(user="", passwd="", db="")
			self.dbc=self.db.cursor()
			self.m_status.AppendText('connected to database\n')
		except Exception as inst:
			self.db = False
			self.dbConnectTryTime = calendar.timegm(time.localtime()) + 60
			self.m_status.AppendText('failed in connecting to database\n')
			print (inst)							
	def handleSerial(self):
		self.RUN_SERIAL = 1
		self.btnEvent = 0
		codeUpdateAcknowledgmentMaxTries = 6
		ser = serial.Serial(2, 9600, timeout=0.1)
		outputs = {'M': '0', 'D': '0', 'E': '0', 'G': '0', 'H': '0', 'B': '0', 'W': '0', 'K': '0', 'L': '0', 'U': '0'}
		phoneNumbers = ['+14155551234', '+14155551234', '+14155551234', '+14155551234']
		keyPadIndexes = {'K': 'D', 'L': 'E'}
		btnEventSerCodes = ['', '.D1', '.M4']
		btnEventDescripts = ['', 'Buzzing front door', 'Setting alarm mode']
		webRoot = "http://www.waywardengineer.com/boxshop/"
		callTimeOut = 0
		startedSkype = 0
		codeToValidate = ''
		codeLocation = 0
		self.mysqlConnect()
			
		while self.RUN_SERIAL:
			value = ser.readline().decode('utf-8')
			if value:
				match = False
				for v in value.split('.'):
					if len(v) > 1:
						outputs[v[0]] = v[1:]
				for k, v in keyPadIndexes.items():
					if len(outputs[k]) > 1:
						codeToValidate = outputs[k]
						codeLocation = k
				if len(codeToValidate) > 0:
					timeStamp = calendar.timegm(time.localtime()) + 7*3600
					sqlString = "SELECT UID, code FROM codes WHERE (startDate = 0 OR (startDate < " + str(timeStamp) + " AND endDate > " + str(timeStamp) + ")) AND keyPad" + codeLocation + " = 1"
					if self.mysqlExecute(sqlString):
						row = self.dbc.fetchone()
	
						while row and not match:
							codeRegex = ''
							codeFromDb = row[1]
							for i in range(len(codeFromDb)):
								codeRegex += codeFromDb[i] + '.*'
							result = re.match(codeRegex, codeToValidate)
							if result:
								match = row[0]
							row = self.dbc.fetchone()
						if match:
							responseCode = "2"
							outputs['U'] = str(match)
						else:
							responseCode = "3"
					else:
						responseCode = "3"							
					outputs[keyPadIndexes[codeLocation]] = responseCode
					serString = "." + keyPadIndexes[codeLocation] + responseCode
					ser.write(serString)
					ser.write(".")
					codeToValidate =''	
				url = webRoot + "alarmapi.php?"
				for k, v in outputs.items():
					url += str(k) + '=' + str(v) + '&'
					if (k != 'M'):
						outputs[k] = '0'
				self.m_status.AppendText(url + '\n')
				try:
					webPage = urllib.urlopen(url)
					webResult = webPage.read()
					if (webResult):
						ser.write(webResult)
					url = webRoot + "codecheck.php"
					webPage = urllib.urlopen(url)
					webResult = webPage.read()
				except Exception as inst:
					self.m_status.AppendText("Got an error contacting the web " + time.strftime("%a, %d %b %Y %H:%M:%S +0000", time.localtime()) + '\n')
					time.sleep(1)
				result = re.match("INSERT|DELETE|UPDATE", webResult)
				if result:
					rows = 0
					for row in webResult.split('|'):
						if len(row) > 0:
							if self.mysqlExecute(row):
								rows += 1
					url += "?recieved=" + str(rows)
					if rows > 0:
						self.m_status.AppendText(url + '\n')
						acknowledgmentSuccess = 0
						acknowledgmentTries = codeUpdateAcknowledgmentMaxTries
						while acknowledgmentSuccess == 0 and acknowledgmentTries > 0:
							try:
								urllib.urlopen(url)
								acknowledgmentSuccess = 1
							except Exception as err:
								self.m_status.AppendText('Error acknowledging code update\n')
								time.sleep(2)
				self.mysqlExecute(False)			
			if outputs['M'] == 4:
				timefucker = calendar.timegm(time.localtime())			
				if callTimeOut == 0:
					callTimeOut = timefucker + 15*60
					phoneNumberIndex = 0
				elif timefucker > callTimeOut:
					self.m_status.AppendText('calling\n')
					if startedSkype:
						sms.Text(phoneNumbers[phoneNumberIndex])
						phoneNumberIndex += 1
						if phoneNumberIndex < len(phoneNumbers):
							callTimeOut =timefucker + 30
						else:
							callTimeOut =timefucker + 15*60
							phoneNumberIndex = 0
					else:
						self.m_status.AppendText('starting skype\n')
						sms = SMS()
						startedSkype = 1						
			else:
				callTimeOut = 0
			if self.btnEvent > 0:
				ser.write(btnEventSerCodes[self.btnEvent])									
				self.m_status.AppendText(btnEventDescripts[self.btnEvent] + '\n')
				self.btnEvent = 0	
			if (not self.db) and self.dbConnectTryTime < calendar.timegm(time.localtime()) :
				self.mysqlConnect()									
			wx.Yield()
class SMS():
	HasConnected = False
	def AttachmentStatusText(self, status):
	   return self.skype.Convert.AttachmentStatusToText(status)
	# This handler is fired when Skype attatchment status changes
	def OnAttach(self, status):
		top.m_status.AppendText( 'API attachment status: ' + self.AttachmentStatusText(status) + '\n')
		if status == Skype4Py.apiAttachAvailable:
			self.skype.Attach()

	def Text(self, phoneNumber):		
		# top.m_status.AppendText('Texting ' + phoneNumber + '..' + '\n')
		message = self.skype.CreateSms(Skype4Py.smsMessageTypeOutgoing, phoneNumber) # create SMS object. CHANGE THE PHONE NUMBER
		message.Body = "Boxshop Alarm is going off. " # set value of body
		message.Send() # send message
	def __init__(self):
		# Creating Skype object and assigning event handlers..
		self.notSetOutputFile = True
		self.skype = Skype4Py.Skype()
		self.skype.OnAttachmentStatus = self.OnAttach

		# Starting Skype if it's not running already..
		if not self.skype.Client.IsRunning:
			top.m_status.AppendText( 'Starting Skype..' + '\n')
			self.skype.Client.Start()
		
		# Attatching to Skype..
		top.m_status.AppendText( 'Connecting to Skype..' + '\n')
		self.skype.Attach()
			
app = wx.App(redirect=True, filename="logfile.txt")
top = Frame("Box Shop Alarm Control")
top.Show()
top.handleSerial()
app.MainLoop()
