from SerialAdaptor import SerialAdaptor
import urllib
from twilio.rest import TwilioRestClient
import re
import time
import os
import threading
import calendar
import json
import hashlib
from Timers import Timer


def processCodeFile(codeFile):
	global codes
	try:
		codes = json.loads(codeFile)
	except ValueError:
		print "Can't parse code file"
		return
	for code in codes:
		code['regex'] = re.compile(''.join([s + '.*' for s in code['code']]))


path = os.path.dirname(os.path.abspath(__file__))
with open(os.path.join(path, 'config.json')) as f:
	config = json.loads(f.read())

with open(os.path.join(path, 'codes.json')) as f:
	codeFile = f.read()

codeHash = hashlib.sha256(codeFile)
processCodeFile(codeFile)

serialAdaptor = SerialAdaptor(config['serial'])
twilioClient = TwilioRestClient(config['twilioAccountSid'], config['twilioAuthToken'])
callTimeOut = 0
systemStatus = {
	'M': '0', #mode
	'D': '0', #yard door
	'E': '0', #shop door
	'G': '0', #yard gate
	'H': '0', #side yard gate
	'B': '0', #beam sensor
	'W': '0', #wire sensor
	'K': '0', #yard keypad
	'L': '0', #shop keypad
	'U': '0'  #user id if valid code
}
keyPadIds = {'K': 'D', 'L': 'E'}


while True:
	result = serialAdaptor.readLine()
	if result:
		print result
		for item in result.split('.'):
			component = item[0]
			componentStatus = item[1:]
			systemStatus[item[0]] = item[1:]
			if component in keyPadIds:
				timestamp = calendar.timegm(time.localtime()) + 7*3600
				responseCode = '3'
				for code in codes:
					if (component in code['locations'] and
								((not code['startDate']) or code['startDate'] < timestamp < code['endDate']) and
								code['regex'].match(componentStatus)):
						responseCode = '2'
						systemStatus['U'] = code['user']
				serialAdaptor.transmitData('.' + keyPadIds[component] + responseCode)
				systemStatus[keyPadIds[component]] = responseCode
		sendStatusUpdate()
		checkCodeStatus()
		for k in ['U', 'K', 'L']:
			systemStatus[k] = '0'
		if systemStatus['M'] == '4':
			timestamp = calendar.timegm(time.localtime())
			if callTimeOut == 0:
				callTimeOut = timestamp + 15*60
				phoneNumberIndex = 0
			elif timestamp > callTimeOut:
				print 'texting'
				for name, phoneNumber in config['phoneNumbers'].items():
					print 'Texting' + name
					message = twilioClient.messages.create(to=phoneNumber, from_=config['fromNumber'], body="Boxshop Alarm!")
				callTimeOut = timestamp + 15*60
			else:
				callTimeOut = 0
			if self.btnEvent > 0:
				self.ser.write(btnEventSerCodes[self.btnEvent])
				self.m_status.AppendText(btnEventDescripts[self.btnEvent] + '\n')
				self.btnEvent = 0
			if (not self.db) and self.dbConnectTryTime < calendar.timegm(time.localtime()) :
				self.mysqlConnect()
			wx.Yield()


