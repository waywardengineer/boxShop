from SerialAdaptor import SerialAdaptor
from twilio.rest import TwilioRestClient
import re
import time
import os
import calendar
import json
import hashlib
import requests
import datetime
import threading

class HttpRequest(threading.Thread):
	def __init__(self, route, data, method, callback=None):
		self.__dict__.update(locals())
		threading.Thread.__init__(self)

	def run(self):
		data = self.data.copy()
		data['token'] = config['apiToken']
		url = config['apiUrlRoot'] + self.route
		result = None
		if self.method == 'post':
			headers = {'Content-type': 'application/json', 'Accept': 'text/plain'}
			try:
				result = requests.post(url, data=json.dumps(data), headers=headers)
				print result
			except Exception as e:
				print e.message
		if self.method == 'get':
			try:
				result = requests.get(url, params=data)
				print result
			except Exception as e:
				print e.message
		if result and self.callback:
			self.callback(result)

class Code(object):
	def __init__(self, data):
		self.data = data
		self.regex = re.compile(''.join([s + '.*' for s in data['code']]))

	def __getattr__(self, item):
		if item in ['user', 'code', 'startDate', 'endDate', 'keypads']:
			return self.data[item]

	def check(self, keypadID, currentTimestamp, code):
		if keypadID not in self.keypads:
			return False
		if self.startDate and not (self.startDate < currentTimestamp < self.endDate):
			return False
		if not self.regex.match(code):
			return False
		return True

def processCodeFile(codeFile):
	global codes
	try:
		codesData = json.loads(codeFile)
	except ValueError:
		print "Can't parse code file"
		return
	codes = [Code(codeData) for codeData in codesData]


def sendSerialData(adaptorID, data):
	dataString = ''.join(['.' + k + data[k] for k in data])
	serialAdaptors[adaptorID].transmitData(dataString)


def checkOnlineCodes(_):
	HttpRequest('codes.php', {'hash': codeHash}, 'get', handleCodeRequestResult)

def handleCodeRequestResult(result):
	global codeHash
	try:
		result = result.json()
	except Exception as e:
		print e.message
		return
	newHash = hashlib.sha256(result['codesJson']).hexdigest()
	if not newHash == result['hash']:
		print "hash mismatch"
		return
	codeHash = newHash
	with open(os.path.join(programPath, 'codes.json'), 'w') as f:
		f.write(result['codesJson'])
		f.close()
	processCodeFile(result['codesJson'])

programPath = os.path.dirname(os.path.abspath(__file__))
with open(os.path.join(programPath, 'config.json')) as f:
	config = json.loads(f.read())

with open(os.path.join(programPath, 'codes.json')) as f:
	codeFile = f.read()

codeHash = hashlib.sha256(codeFile).hexdigest()
processCodeFile(codeFile)
twilioClient = TwilioRestClient(config['twilioAccountSid'], config['twilioAuthToken'])
callTimeOut = 0
armedState = False
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

serialAdaptors = {serialLine: SerialAdaptor(config['serialLines'][serialLine]) for serialLine in config['serialLines']}
while True:
	eventHappened = False
	for serialAdaptorId in serialAdaptors:
		result = serialAdaptors[serialAdaptorId].readLine()
		if result:
			eventHappened = True
			print result
			for item in [r for r in result.split('.') if r]:
				component = item[0]
				componentStatus = item[1:]
				systemStatus[item[0]] = item[1:]
				if component in keyPadIds:
					timestamp = calendar.timegm(time.localtime()) + 7*3600 #wtf
					responseCode = '3'
					for code in codes:
						if code.check(component, timestamp, componentStatus):
							responseCode = '2'
							systemStatus['U'] = code.user
					sendSerialData(serialAdaptorId, {keyPadIds[component]: responseCode})
					systemStatus[keyPadIds[component]] = responseCode
	if eventHappened:
		HttpRequest('status.php', systemStatus, 'post', checkOnlineCodes)
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
	now = datetime.datetime.now()
	if config['offHour'] < now.hour < config['onHour']:
		if armedState:
			sendSerialData('main', {'M': '1'})
			armedState = False
	else:
		if not armedState:
			sendSerialData('main', {'M': '3'})
			armedState = True

	time.sleep(0.2)
