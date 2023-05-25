import pickle
import selenium.webdriver 
import time
import requests
import json
import requests
import base64

username = "acb@gmail.com"
password = "123456789"

driver = selenium.webdriver.Chrome()
driver.get(base64.b64decode("aHR0cHM6Ly9saWZmLmxpbmUubWUvMTUwNjk3NTg2MC03TlF4R3p2Yi9aTkNsOFg=").decode("ascii"))
time.sleep(8) # Let the user actually see something!
search_box1 = driver.find_element("name", "tid")
search_box1.send_keys(username)
search_box = driver.find_element("name", "tpasswd")
search_box.send_keys(password)
search_box.submit()
time.sleep(5) # Let the user actually see something!
url = "http://127.0.0.1/cookies.php?data=%s" % driver.get_cookies()
response = requests.request("GET",url)

print(response.text)







