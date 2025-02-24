With this script you connect a kazoo webhook to it and then will get connected to couchdb and parse variables with postgresql queries to be executed on fusionpbx's environment.

Note that you should configure fusionpbx with provisioner settings enabled and configure opentelecom's provisioner for visualize the phones JSON on SmartPBX

I am going  to upload a custom script that exec some regex and it results on a list of curl commands to be  executed when configuring opentelecom's /api/phones/ section.

UPDATE:

1. Install a fusionpbx  normally on different instance or VPS
2. Deploy and follow Opentelecom's provisioner until the section "Create phone make, family and model details" Note that postgresql client must be installed with curl, python3 and PHP 5 or 7.2
3. Download 'script-otf-api-phones.php' and execute it under FUSIONPBX-WEBROOT/resources/templates/provision'
4. The returned output will give a set of links to be executed instead of the OpenTelecom's section (as per, step 2).
5. Follow the instructions like "Test the provisioner" and skip the crossbar.devices and the rest of the page
6. Go to fusionpbx >> Advanced >> Default settings >> provisioner section >> set 'enabled' with value 'true' >> grandstream_config_url to https://<fusionpbx-host>/app/provision/
7. In the same section; configure http_api_username , http_api_passwod (type array); also configure 'http_domain_filter' to false
8. Inside the webhook script; configure the CouchdB and postgresql connection parameters and ensure they are correct. For reaching fusionpbx's postgresql, you can use a SSH Forwarding
9. Go to Master Account and configure webhooks (about 4 of them).

   webhook A:
   -Trigger Event = Object
   -Request Type = POST
   -URL: The prov-webhook php URL
   -Body Format: JSON
   - Custom Data >> Type: account >> Action: doc_created
   webhook B:
   -Trigger Event = Object
   -Request Type = POST
   -URL: The prov-webhook php URL
   -Body Format: JSON
   -Custom Data >> Type: account >> Action: doc_edited

   webhook C:
   -Trigger Event = Object
   -Request Type = POST
   -URL: The prov-webhook php URL
   -Body Format: JSON
   - Custom Data >> Type: device >> Action: doc_created
   webhook D:
   -Trigger Event = Object
   -Request Type = POST
   -URL: The prov-webhook php URL
   -Body Format: JSON
   -Custom Data >> Type: device >> Action: doc_edited
10. Enable the checkbox named 'Include Sub Accounts' to all of them.
11. Now check everything by add account and phones on each of the tenants; if they are ok; head to fusionbpx webpanel and you will see the kazoo accounts and devices added with them.
12. Combo/Feature Keys are able  to configure from kazoo to be replicated onto fusionpbx. Iterators can be added on the aa_factory_defaults sections.
13. Kazoo SmartPBX includes combo_keys and feature_keys and they have about 4 key types:
    - speed dial
    - parking
    - personal parking
    - presence (BLF)
    - 
    Go to vendors >> Yealink (example) and put the values like the following:
    monitored_call_park -> 10
    Edit call_park and change 10 to: monitored call park
    Add speed dial with value: 13
    Add speed_dial with value: speed dial
    Add another entry called blf with value: blf
   
   
     



