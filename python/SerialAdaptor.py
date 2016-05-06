import serial


class SerialAdaptor(object):
	def __init__(self, config):
		self.config = config
		self.connection = None
		self.connect()

	def transmitData(self, data):
		try:
			self.connection.write(data)
			return True
		except serial.SerialTimeoutException, serial.SerialException:
			if self.connect():
				try:
					self.connection.write(data)
					return True
				except serial.SerialTimeoutException, serial.SerialException:
					return False

	def readLine(self):
		try:
			return self.connection.readline().decode('utf-8')
		except serial.SerialTimeoutException, serial.SerialException:
			if self.connect():
				try:
					return self.connection.readline().decode('utf-8')
				except serial.SerialTimeoutException, serial.SerialException:
					return False

	def connect(self):
		if self.connection:
			self.connection.close()
			self.connection = None
		portIndex = 0
		while (not self.connection) and portIndex < len(self.config['ports']):
			try:
				self.connection = serial.Serial(self.config['ports'][portIndex], self.config['baudrate'], timeout=0.1)
				return True
			except serial.SerialException:
				portIndex += 1
		return False
