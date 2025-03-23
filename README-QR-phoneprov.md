Welcome!

This webhook script can be setup with the purpose of sending QR code softphone/smartphone credentials throught the user's email.
The softphone used is PortSIP Softphone, it has the functionality of capture QR Code and set up phone credentials through it.

1. Clone or copy the php script to a webroot with nginx and php enabled
2. Install nginx and php-fpm, also install php-composer,
3. Install QR Code for php (https://github.com/drbiko/php-qr-code/blob/master/README.md)
4. From a public facing web-server, configure a proxy-pass that points to the qr code webhook directory
   location /qr/ {
	proxy_pass http://webhook.tld/ ;
	}
5. Edit the script and change the printftestsvg variable with URL value as your.domain.tld/qr/ :  
6. Create a webhook with the following properties (master account)
- Name: my-qr-code-webhook
- Trigger Event: Object
- Tick 'Include Sub-Accounts
- Request Type: POST
- Body Format: JSON
- Type: Device
- Action: doc_created (it can be doc_edited for testing purposes, just change to this value by editing the script)
- Click Create Webhook

7. Add an user with a valid Email and set an extension number to it
8. Add a device of type softphone or smartphone from the SmartPBX's users section >> Selected User >> Devices >> New Device
9. Save it.
10. Then you will receive an email with the QR Code Information 
