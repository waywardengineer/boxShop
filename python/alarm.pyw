import serial
import urllib
import sys
import Skype4Py
if sys.version_info >= (3, 0):
	import urllib.request
	gui = 0 
else:
	gui = 1
from time import strftime, gmtime, sleep, localtime, time

# mode 0=error, 1-5
# door 0=closed 1=open
# gate 0=closed 1=open
# laser beam 0=fine 1=interrupted
# wire 0=fine 1=interrupted
# keypad 0=nothing/1=good/2=bad password
# code string-of-digits (5 or more)

PAUSE_SERIAL = 0
justStarted = 1
if gui:
	import wx
	ID_START = wx.NewId()
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
			
				
	class Frame(wx.Frame):
		global Phone, call
		def __init__(self, title):
			wx.Frame.__init__(self, None, title=title, size=(750,300))
			panel = wx.Panel(self)
			box = wx.BoxSizer(wx.VERTICAL)
			m_text = wx.StaticText(panel, -1, "Box Shop Alarm Control")
			m_text.SetFont(wx.Font(14, wx.SWISS, wx.NORMAL, wx.BOLD))
			m_text.SetSize(m_text.GetBestSize())
			box.Add(m_text, 0, wx.ALL, 10)

			self.m_status = wx.TextCtrl(panel, style=wx.TE_MULTILINE, pos=(100, 50), size=(600, 150))
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
			self.m_close.Disable()
			self.m_open.Enable()
			
		def OnStart(self, event):
			self.m_open.Disable()
			self.m_close.Enable()
			self.handleSerial()

	

			
		def OnBuzz(self, event):
			self.RUN_SERIAL = 2
		def OnAlarm(self, event):
			self.RUN_SERIAL = 3
			
						
		def handleSerial(self):
			global Phone, call
			self.RUN_SERIAL = 1
			ser = serial.Serial(2, 9600, timeout=0.1)
			outputs = {'M': '0', 'D': '0', 'G': '0', 'B': '0', 'W': '0', 'K': '0', 'C': '0'}
			phoneNumbers = ['+14155551212', '+14155551212', '+14155551212', '+14155551212']
			callTimeOut = 0
			startedSkype = 0
			while self.RUN_SERIAL:
				if self.RUN_SERIAL == 2:
					self.m_status.AppendText("WE ARE BUZZING!!!!" + '\n')
	
					s = '.D1'
					if (s):
						ser.write(s)
										
					self.m_status.AppendText("We buzzed." + '\n')
					self.RUN_SERIAL = 1
				elif self.RUN_SERIAL ==3:
					self.m_status.AppendText("Alarm mode!" + '\n')
	
					s = '.M4'
					if (s):
						ser.write(s)
										
					self.m_status.AppendText("Alarm." + '\n')
					self.RUN_SERIAL = 1				
				else:
					value = ser.readline().decode('utf-8')
					if value:
						for v in value.strip('{}').split('}{'):
							outputs[v[0]] = v[1:]
						url = "http://www.waywardengineer.com/boxshop/alarmapi.php?"
						for k, v in outputs.items():
							url += k + '=' + v + '&'
						outputs['K'] = '0'
						outputs['C'] = '0'
						self.m_status.AppendText(url + '\n')
						try:
							if sys.version_info >= (3, 0):
								f = urllib.request.urlopen(url)
							else:
								f = urllib.urlopen(url)
							s = f.read()
							#print (s)
							if (s):
								ser.write(s)
						except Exception as inst:
							self.m_status.AppendText("Got an error contacting the web " + strftime("%a, %d %b %Y %H:%M:%S +0000", localtime()) + '\n')
							#self.m_status.AppendText(type(inst))
							#self.m_status.AppendText(inst.args)
							#self.m_status.AppendText(inst)
							sleep(1)
					#outputs['M'] = 4
					if outputs['M'] == 4:
						if callTimeOut == 0:
							callTimeOut = time() + 15*60
							phoneNumberIndex = 0
						elif time() > callTimeOut:
							if startedSkype:
								sms.Text(phoneNumbers[phoneNumberIndex])
								phoneNumberIndex += 1
								if phoneNumberIndex < len(phoneNumbers):
									callTimeOut = time() + 30
								else:
									callTimeOut = time() + 15*60
									phoneNumberIndex = 0
							else:
								sms = SMS()
								startedSkype = 1						
					else:
						callTimeOut = 0
				wx.Yield()					
	app = wx.App(redirect=True)
	top = Frame("Box Shop Alarm Control")
	top.Show()
	top.handleSerial()
	app.MainLoop()
else:
	class SerialWebBridge:
		def handleSerial(self):
			self.RUN_SERIAL = 1
			ser = serial.Serial(2, 9600, timeout=1)
			outputs = {'M': '0', 'D': '0', 'G': '0', 'B': '0', 'W': '0', 'K': '0', 'C': '0'}
			while self.RUN_SERIAL:
				value = ser.readline().decode('utf-8')
				if value:
					for v in value.strip('{}').split('}{'):
						outputs[v[0]] = v[1:]
					url = "http://www.waywardengineer.com/boxshop/alarmapi.php?"
					for k, v in outputs.items():
						url += k + '=' + v + '&'
					outputs['K'] = '0'
					outputs['C'] = '0'
					print(url)	
					try:
						if sys.version_info >= (3, 0):
							f = urllib.request.urlopen(url)
						else:
							f = urllib.urlopen(url)
						s = f.read()
						print (s)
						if (s):
							ser.write(s)
					except Exception as inst:
						print("Got an error contacting the web " + strftime("%a, %d %b %Y %H:%M:%S +0000", gmtime()))
						print(type(inst))
						print(inst.args)
						print(inst)
						sleep(1)

	bridge = SerialWebBridge()
	bridge.handleSerial()
			