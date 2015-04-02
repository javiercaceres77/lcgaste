#!/usr/bin/env python

import serial
cc128 = serial.Serial("/dev/ttyUSB0", 57600, timeout=6)
cc128xml = cc128.readlines(6)
print cc128xml

