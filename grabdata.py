#pip install PyVirtualDisplay
#xvfb-run --auto-servernum -s '-screen 0 1920x1080x24' python -u ./grabdata.py 2>&1
#export DISPLAY=:0.0 

import os
import time
import datetime
import json
import sys
from selenium import webdriver
from selenium.webdriver.common.keys import Keys
import logging
from pyvirtualdisplay import Display 
import sys
import codecs
try:
    from urllib.parse import urlparse
except ImportError:
     from urlparse import urlparse
import base64

sys.stdout = codecs.getwriter('utf8')(sys.stdout)
sys.stderr = codecs.getwriter('utf8')(sys.stderr)

cmd1 = os.system("killall /bin/chromedriver >/dev/null 2>&1")
cmd2 = os.system("killall /opt/google/chrome-beta/chrome >/dev/null 2>&1")
cmd3 = os.system("killall Xvfb >/dev/null 2>&1")

display = Display(visible=0, size=(800, 600))
display.start()



optionsc = webdriver.ChromeOptions()  
optionsc.add_argument('--port=9515');
optionsc.add_argument('--window-size=500x400');
optionsc.add_argument('--ignore-certificate-errors') 
optionsc.add_argument('--headless')
optionsc.add_argument('--disable-gpu') 
#optionsc.add_argument("--remote-debugging-port=9229");
optionsc.add_argument("--remote-debugging-port=1557");
optionsc.add_argument('--no-sandbox') # required when running as root user. otherwise you would get no sandbox errors. 
optionsc.add_argument("--disable-extensions");
#optionsc.setExperimentalOption("debuggerAddress", "127.0.0.1:1557");
#optionsc.set_binary("/bin/google-chrome-beta");

driver = webdriver.Chrome(options=optionsc,executable_path = '/bin/chromedriver',service_args=['--verbose --port=9515', '--log-path=/tmp/chromedriver.log'])

driver.set_page_load_timeout(30)

try:
	url=str(sys.argv[1])

	#print(url)
	driver.get(url);
	time.sleep(1) # Pausa di 1 secondo prima del prossimo comando
 
	html_source = driver.page_source

	print(html_source ) 
except Exception:
     print "grabdata.py script error (exception of:"+str(sys.argv[1])+")"
	 

driver.quit()

display.stop()
 
sys.exit()

