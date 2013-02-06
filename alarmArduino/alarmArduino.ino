// vim:set ts=2 sw=2 ai et syntax=c:

// Uses the keypad library that came from http://www.arduino.cc/playground/Code/Keypad



#define KPTIMEOUT  (30000)  // If no key is pressed for this many milliseconds, clear the input buffer
#define KPMAXTRIES 6 // max number of failed tries before timeout happens
#define KPMAXTRYTIMEINT (30000) //time it takes for 1 failed password attempt to be deducted from the total; the timeout length is 3 times this
#define KPPASSWORDLEN 5 //number of chars in a password including the #
#define KPNUMPASSWORDS 2 //number of passwords- if i were better at array stuff we probably could ditch this
#define LATCHDELAY 6000
/*
 * Passwords must end in '#'
 *
 * When you're entering a password at the gate, it's a good idea to start
 * by hitting '#', too, to flush the buffer of any password someone was
 * prevriously trying to enter.
 */
#define NUMALARMSENSEINPUTS 3
#define HIGHILLUMLEDTIMEOUT 5000
#define WAITCOUNTDOWNLENGTH 30000
#define ALARMCODECOUNTDOWNLENGTH 10000 //length of time to flash alarm code after alarm has been turned off
#define ALARMSTAGE1LENGTH 20000
#define ALARMSTAGE2LENGTH 75000
#define KPCODECHECKDELAY 60000
#define ALARMWEBPOLLINTERVAL 30000
#define WEBPOLLINTERVAL 30*60000
#define SERBUFFERSENDSPACING 5000
char passwords[][KPPASSWORDLEN] = {{'1','2','3','4','#'}, {'4','3','2','1','#'}};

#include "Keypad.h"

const byte rows = 4;
const byte cols = 3;
char keys[rows][cols] = {
  {
    '1','2','3'  }
  ,
  {
    '4','5','6'  }
  ,
  {
    '7','8','9'  }
  ,
  {
    '*','0','#'  }
};
int serialCharCount;
byte rowPins[rows] = {
  48, 47, 46, 45};
byte colPins[cols] = {
  51, 50, 49};

Keypad keypad = Keypad(makeKeymap(keys), rowPins, colPins, rows, cols);

char kpBuffer[10];
char key = NO_KEY;
char kpLastKey = NO_KEY;
unsigned long upTime = 0;
unsigned long lastUpTime = 0;
unsigned long kpLastKeyTime = 0;
unsigned long kpFailClearTime = 0;
unsigned long beepTimeOut = 0;
unsigned long serBufferTimeOut = 0;
byte kpFailCount = 0;
byte kpInTimeOut = false;
int kpBufferLen;
byte activeTimeOut = false;
byte alarmStage;
byte incomingCommandType;
byte incomingCommand;




const byte oNotArmedLed = 5;
const byte oSensorsLed = 6;
const byte oWaitLed = 7;
const byte oArmedLed = 8;
const byte oIllumLed = 9;
const byte oHighIllumLed = 10;
const byte oBeep = 11;



const byte oAlarmBell = 22;
const byte oAlarmScreech = 12;
const byte oDoorLatch = 24;


const byte iGateSwitch = 26;
const byte iDoorSwitch = 27;
const byte iBeamSensor = 28;
const byte iWireCutSensor = 29;
const byte iOffSwitch = 30;
const byte iTimerSwitch = 31;
const byte iExitButton = 32;
const byte iDoorBell = 33;

int alarmInputs[NUMALARMSENSEINPUTS][2] = {{iDoorSwitch, 500}, {iGateSwitch, 1500}, {iBeamSensor, 1500}};
char alarmInputSerialCodes[NUMALARMSENSEINPUTS][2] = {{'D', '0'}, {'G', '0'}, {'B', '0'}};
int beepRepeatCount;
int mode;
int sensorTripped;
unsigned long modeCountDown = 0;
unsigned long ledNextEventTime = 0;
unsigned long highIllumLedTimeOut = 0;
unsigned long latchTimeOut = 0;
unsigned long alarmTimeOut = 0;
unsigned long newAlarmTimeOut = 0;
unsigned long codeCheckTimeOut = 0;
unsigned long webPollTimeOut = 0;
int ledBlinkCount = 0;
int ledBlink = LOW;
int beepActiveType;
String serBuffer = String("");
byte kpGoodPass = false;
int i;
int beepTypes[2][4] = {
  {
    3000, 100, 150, 3  }
  , {
    4000, 500, 1000, 15  }
}; //frequency, ontime, cycletime, repeat

#define NUMDEBOUNCEDINPUTS 2
#define DEBOUNCEDELAY 50
byte deBouncePinIndex[50];
byte deBounceLastState[NUMDEBOUNCEDINPUTS];
byte deBounceWait[NUMDEBOUNCEDINPUTS];
unsigned long deBounceTimeOut[NUMDEBOUNCEDINPUTS];
int incomingByte;

void setup() {
  Serial.begin(9600);
  pinMode (oAlarmBell,OUTPUT);
  pinMode (oAlarmScreech,OUTPUT);
  pinMode (oHighIllumLed,OUTPUT);
  pinMode (oIllumLed,OUTPUT);
  pinMode (oSensorsLed,OUTPUT);
  pinMode (oWaitLed,OUTPUT);
  pinMode (oNotArmedLed,OUTPUT);
  pinMode (oArmedLed,OUTPUT);
  pinMode (oDoorLatch,OUTPUT);
  pinMode (oBeep,OUTPUT);

  pinMode (iGateSwitch,INPUT);
  pinMode (iDoorSwitch,INPUT);
  pinMode (iBeamSensor,INPUT);
  pinMode (iWireCutSensor,INPUT);
  pinMode (iOffSwitch,INPUT);
  pinMode (iTimerSwitch,INPUT);
  pinMode (iExitButton,INPUT);
  pinMode (iDoorBell,INPUT);
  kpClearBuffer();
  changeMode(1);

}

void loop() {
  lastUpTime = upTime;
  upTime = millis();
  if (upTime < lastUpTime) { // we wrapped!  special case.
    doWrap();
  } 
    // keypad stuff
  if (kpBuffer[0] && upTime - kpLastKeyTime > KPTIMEOUT) {
    kpClearBuffer();
  }
  if (kpFailCount > 0 && upTime > kpFailClearTime){
    if (kpInTimeOut && kpFailCount < KPMAXTRIES){  
      kpInTimeOut = false;
    }
    kpFailCount--;
    kpFailClearTime = upTime + KPMAXTRYTIMEINT;
  }
  kpLastKey = key;
  key = keypad.getKey();
  if (key != NO_KEY) {
    highIllumLedTimeOut = upTime + HIGHILLUMLEDTIMEOUT;
    digitalWrite(oHighIllumLed, HIGH);
    activeTimeOut = true;
    if (kpLastKey != key) {
      kpLastKeyTime = upTime;
      if (!kpInTimeOut){
        
        if (kpBufferLen <= sizeof(kpBuffer) - 1){
          kpBuffer[kpBufferLen++] = key;
          tone(oBeep, 3000, 50);
        }  
        if (key == '#') {
          kpCheckPassword();
          kpClearBuffer();
        }
        if (key == '*') {
          checkSensors(true);
        }
      }
      else{
        tone(oBeep, 3000, 1000); //long beep to say "i'm not listening anymore, you fool!"
      }       
    }
  }

  // alarm status stuff
  switch(mode){
  case 1: //not armed
    if (digitalRead(iTimerSwitch)){
      changeMode(3);
    }
    checkSensors(false);
    if (digitalRead(iDoorBell)){
      digitalWrite(oAlarmBell, HIGH);
    }
    else {
      digitalWrite(oAlarmBell, LOW);
    }      
    break;
  case 2: //armed
    digitalWrite(oArmedLed, blinkLeds(1000, 1000, 0, 1));
    if (!digitalRead(iTimerSwitch)){//timer
      changeMode(1);
    }
    if (digitalRead(iExitButton) || kpGoodPass){//exitbtn
      changeMode(3);
    }
    if (!checkSensors(false) && alarmTimeOut <= upTime){
      changeMode(4);
    }
    break;
  case 3: //wait  
    digitalWrite(oWaitLed, blinkLeds(150, 1000, 200, (sensorTripped + 1)));
    if(!checkSensors(false) || digitalRead(iExitButton) || kpGoodPass){
      modeCountDown = upTime + WAITCOUNTDOWNLENGTH;
    }
    if (modeCountDown < upTime) {
      changeMode(2);
    }
    if (!digitalRead(iTimerSwitch)){
      changeMode(1);
    }

    
    break;
  case 4: //alarm
    if (blinkLeds(500, 500, 0, 1)){
      digitalWrite(oIllumLed, HIGH);
      digitalWrite(oArmedLed, LOW);
    }
    else{
      digitalWrite(oIllumLed, LOW);
      digitalWrite(oArmedLed, HIGH);
    }
    if (alarmStage == 0 && modeCountDown < upTime){
      digitalWrite (oAlarmBell, HIGH);
      alarmStage++;
      modeCountDown = upTime + ALARMSTAGE2LENGTH;
    }
    if (alarmStage == 1 && modeCountDown < upTime){
      digitalWrite (oAlarmScreech, HIGH);
      alarmStage++;
    }
    if (upTime > webPollTimeOut){
      serBuffer += String("{M4}");
      webPollTimeOut = upTime + ALARMWEBPOLLINTERVAL;
    }
    if (digitalRead(iOffSwitch) || kpGoodPass){
      changeMode(5);
    }
    break;
  case 5: //stop alarm & blink alarm code
    digitalWrite(oArmedLed, blinkLeds(100, 1000, 200, sensorTripped));
    if (modeCountDown < upTime) {
      changeMode(3);
    }
    break;
  }
  if (activeTimeOut){
    if (upTime >= latchTimeOut || (latchTimeOut > 0 && !digitalRead(iDoorSwitch))){
      digitalWrite(oDoorLatch, LOW);
      latchTimeOut = 0;
    }
    if (upTime >= highIllumLedTimeOut){
      digitalWrite(oHighIllumLed, LOW);
      highIllumLedTimeOut = 0;
    }
    if (upTime >= codeCheckTimeOut && codeCheckTimeOut > 0){
      kpDoBadPass();
      codeCheckTimeOut = 0;
    }
      
    if (latchTimeOut == 0 && highIllumLedTimeOut == 0 && codeCheckTimeOut == 0){
      activeTimeOut = false;
    }
  }
  if (beepActiveType >= 0){
    if (upTime >= beepTimeOut){
      if (beepRepeatCount > 0){
        tone(oBeep, beepTypes[beepActiveType][0], beepTypes[beepActiveType][1]);
        beepTimeOut = upTime + beepTypes[beepActiveType][2];
        beepRepeatCount--;
      }
      else {
        beepActiveType = -1;
      }
    }
  }
  if (mode != 4 && webPollTimeOut <upTime){
    checkSensors(true);
    webPollTimeOut = upTime + WEBPOLLINTERVAL;
  }
  kpGoodPass = false;
  while (Serial.available() > 0) {
    // read the incoming byte:
    incomingByte = Serial.read();
    // say what you got:
    if (incomingByte == '.'){
      serialCharCount = 0;
    }
    else if (serialCharCount == 1){
      incomingCommandType = incomingByte;
    }
    else if (serialCharCount == 2){
      incomingCommand = incomingByte;
      processCommand();
    }
    serialCharCount++;
  }
  if (serBuffer != String("") && upTime > serBufferTimeOut){
    Serial.print(serBuffer);
    serBuffer = String("");
    serBufferTimeOut = upTime + SERBUFFERSENDSPACING;
  }
    
}
void processCommand(){
  switch (incomingCommandType){
    case 'D':
      if (incomingCommand == '1'){
        kpGoodPass = true;
        kpDoGoodPass();
      }
      else {
        kpDoBadPass();
      }
      break;
    case 'M':
      if (incomingCommand == '3' && mode == 4){
        changeMode(3);
      }
      if (incomingCommand == '4'){
        changeMode(4);
      }
      break;    
    case 'R':
      serBuffer = String("{M") + String(mode) + String("}");
      checkSensors(true);
      break;
    }
}
    
  
  

void kpClearBuffer() {
  memset(kpBuffer, 0, sizeof(kpBuffer));
  kpBufferLen = 0;
}

void kpCheckPassword() {
  int i;
  int j=0;
  int passcharcount;
  while(j<KPNUMPASSWORDS && !kpGoodPass){
    passcharcount=0;
    i=0;
    while(i <= kpBufferLen) {
      if (passwords[j][passcharcount] == kpBuffer[i]) {//matched this character, go to next one
        passcharcount++;
      }
      i++;
    }
    if (passcharcount==KPPASSWORDLEN){
      kpGoodPass = true;
    }
    j++;
  }
  if (kpGoodPass){
    kpDoGoodPass();
  }
  else if (kpBufferLen > 5){
    serBuffer += String("{C");
    kpBufferLen--;
    for (i=0; i<kpBufferLen; i++){
      serBuffer += String(kpBuffer[i]);
    }
    serBuffer += String("}");
    codeCheckTimeOut = upTime + KPCODECHECKDELAY;
    activeTimeOut = true;    
      
  }
  else if (kpBufferLen > 1){ //don't count bad password if only # was pressed
    kpDoBadPass();  
  }
}
void kpDoGoodPass (){
    kpFailCount=0;
    digitalWrite(oDoorLatch, HIGH);
    activeTimeOut = true;
    latchTimeOut = upTime + LATCHDELAY;
    codeCheckTimeOut = 0;
    serBuffer += String("{K1}");
    doBeep(0);
}
void kpDoBadPass (){
    tone(oBeep, 3000, 500);
    kpFailCount++;
    kpFailClearTime=upTime + KPMAXTRYTIMEINT;
    if (kpFailCount > KPMAXTRIES){
      kpInTimeOut=true;      
    }
    serBuffer += String("{K2}");
}

void doBeep(int beepTypeIn){
  beepActiveType = beepTypeIn;
  beepRepeatCount = beepTypes[beepTypeIn][3];
  beepTimeOut = upTime;
}
int changeMode(int modeIn){
  resetBlinkLeds();
  if (mode == 4){
    noTone(oBeep);
    beepActiveType = -1;
  }
  mode = modeIn;
  digitalWrite(oSensorsLed, LOW);
  digitalWrite(oAlarmBell, LOW);
  digitalWrite(oAlarmScreech, LOW);
  digitalWrite(oIllumLed, HIGH);
  switch (modeIn){
  case 1:
    digitalWrite(oWaitLed, LOW);   
    digitalWrite(oNotArmedLed, HIGH);
    digitalWrite(oArmedLed, LOW);
    break;
  case 2:
    digitalWrite(oWaitLed, LOW);    
    digitalWrite(oNotArmedLed, LOW);
    digitalWrite(oArmedLed, HIGH);
    sensorTripped=0;
    break;
  case 3:
    digitalWrite(oWaitLed, HIGH);  
    digitalWrite(oNotArmedLed, LOW);
    digitalWrite(oArmedLed, LOW);
    modeCountDown = WAITCOUNTDOWNLENGTH + upTime;
    break;
  case 4:
    alarmStage = 0;
    modeCountDown = ALARMSTAGE1LENGTH + upTime;
    digitalWrite(oWaitLed, LOW);
    digitalWrite(oNotArmedLed, LOW);
    digitalWrite(oArmedLed, HIGH);
    webPollTimeOut = upTime + ALARMWEBPOLLINTERVAL;
    doBeep(1);
    break;
  case 5:
    digitalWrite(oWaitLed, LOW);
    digitalWrite(oNotArmedLed, LOW);
    digitalWrite(oArmedLed, LOW);
    modeCountDown = ALARMCODECOUNTDOWNLENGTH + upTime;
    break;
  }
  serBuffer += String("{M");
  serBuffer += String(mode);
  serBuffer += String("}");

}
int checkSensors(byte reportAll){
    if (!reportAll){
      sensorTripped = 0;
    }
    int thisStatus;
    for (i = 0; i < NUMALARMSENSEINPUTS; i++){
      if (!digitalRead(alarmInputs[i][0])){
        sensorTripped = i + 1;
        newAlarmTimeOut = upTime + alarmInputs[i][1];
        if (newAlarmTimeOut < alarmTimeOut || alarmTimeOut == 0){
          alarmTimeOut = newAlarmTimeOut;
        }
        thisStatus = '1';
      }
      else {
        thisStatus = '0';
      }
      if (alarmInputSerialCodes[i][1] != thisStatus || reportAll){
        alarmInputSerialCodes[i][1] = thisStatus;
        serBuffer += String("{");
        serBuffer += String(alarmInputSerialCodes[i][0]);
        serBuffer += String(alarmInputSerialCodes[i][1]);
        serBuffer += String("}");
      } 
    }
    if (sensorTripped == 0){
      alarmTimeOut = 0;
      return true;
    }
    else {
      return false;
    }
}

int blinkLeds(unsigned long onTime, unsigned long longOffTime, unsigned long shortOffTime, int numBlinks){
  if (ledNextEventTime < upTime) {
    if (ledBlink) {
      ledBlink=LOW;
      ledBlinkCount--;
      if (ledBlinkCount > 0){
        ledNextEventTime = upTime + shortOffTime;
      }
      else{
        ledBlinkCount = numBlinks;
        ledNextEventTime = upTime + longOffTime;
      }
    }
    else{
      ledBlink = HIGH;
      ledNextEventTime = upTime + onTime;
    }
  }
  return ledBlink;
}
void resetBlinkLeds(){
  ledNextEventTime = 0;
  ledBlink = LOW;
  ledBlinkCount = 0;
}
void doWrap(){
  kpLastKeyTime = 0;
  kpFailClearTime = 0;
  kpClearBuffer();
  unsigned long modeCountDown = 0;
  ledNextEventTime = 0;
  highIllumLedTimeOut = 0;
  latchTimeOut = 0;
  alarmTimeOut = 0;
  codeCheckTimeOut = 0;
  webPollTimeOut = 0;
  activeTimeOut = false;
  noTone(oBeep);
  beepActiveType = -1;
  digitalWrite(oDoorLatch, LOW);
}


