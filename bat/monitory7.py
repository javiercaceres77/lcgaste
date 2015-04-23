#!/usr/bin/env python

import serial
import datetime

try:
	cc128 = serial.Serial("/dev/ttyUSB0", 57600, timeout=7)
except serial.SerialException, msg:
	my_msg = str(msg)
	exit(1)
	
while True:
	now = datetime.datetime.now()
	strdate = now.strftime("%Y%m%d")
	strhour = now.strftime("%H")
	#strtime = now.strftime("%H%M")

	#filename = "/media/usb/ccfiles/currcost_" + strdate + "_" + strhour + "00.dat"
	filexml = "/home/javipi/ccxmls/currxml_" + strdate + "_" + strhour + "00.dat"
		
	#f = open(filename, "a")
	fxml = open(filexml, "a")
	
	counter = 10
	while counter > 0:
		counter-=1

		line = cc128.readline()
		time = ""
		hour = ""
		tmpr = ""
		watts = ""

		timest = line.find("<time>") + len("<time>")
		if timest > len("<time>"):
			timeend = line.find("</time>", timest)
			if timeend <> -1:
				time = line[timest:timeend]
				hour = time[0:2]
				
	#	tmprst = line.find("<tmpr>") + len("<tmpr>")
	#	if tmprst > len("<tmpr>"):
	#		tmprend = line.find("</tmpr>", tmprst)
	#		if tmprend <> -1:
	#			tmpr = line[tmprst:tmprend]
	#	
	#	wattsst = line.find("<watts>") + len("<watts>")
	#	if wattsst > len("<watts>"):
	#		wattsend = line.find("</watts>", wattsst)
	#		if wattsend <> -1:
	#			watts = line[wattsst:wattsend]
    #
	#	if watts <> "" and hour == strhour:
	#		f.write(strdate + " " + time + ";" + tmpr + ";" + watts + "\n")
		
		if hour == strhour:
			fxml.write(line)
		
		#print (str(counter) + ": strhour: "+ strhour + "; hour: " + hour + " strdate: " + strdate + " xx: " + time + ";" + tmpr + ";" + watts + "\n")
	#f.close()
	fxml.close()
	
exit(0)
	
