## Welcome!

<p> This webhook script can be setup with the purpose of sending QR code softphone/smartphone credentials throught the user's email.
The softphone used is PortSIP Softphone, it has the functionality of capture QR Code and set up phone credentials through it. </p>

1. Configure  email relay with an account to be able to send emails; it can be SSMTP or Postfix
2. Clone or copy the php script to a webroot with nginx and php enabled
3. Install nginx and php-fpm, also install php-composer,
4. Install QR Code for php (https://github.com/drbiko/php-qr-code/blob/master/README.md)
5. From a public facing web-server, configure a proxy-pass that points to the qr code webhook directory
   location /qr/ {
	proxy_pass http://webhook.tld/ ;
	}
6. Edit the script and change the printftestsvg variable with URL value as your.domain.tld/qr/ :  
7. Create a webhook with the following properties (master account)
- Name: my-qr-code-webhook
- Trigger Event: Object
- Tick 'Include Sub-Accounts
- Request Type: POST
- Body Format: JSON
- Type: Device
- Action: doc_created (it can be doc_edited for testing purposes, just change to this value by editing the script)
- Click Create Webhook

8. Add an user with a valid Email and set an extension number to it
9. Add a device of type softphone or smartphone from the SmartPBX's users section >> Selected User >> Devices >> New Device
10. Save it.
11. Then you will receive an email with the QR Code Information 
