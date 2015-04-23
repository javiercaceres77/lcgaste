#!/usr/bin/env python

import datetime
import time

now = datetime.dst()
print now.strftime("%H")

nowgmt = time.localtime()
print nowgmt
