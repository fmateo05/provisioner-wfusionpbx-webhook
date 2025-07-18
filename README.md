### Kazoo Provisioner with FusionPBX Backend.


[![Demo Video](https://i.ytimg.com/an_webp/SiMvXK41jdM/mqdefault_6s.webp?du=3000&sqp=CLC6j8EG&rs=AOn4CLChCjDzRVUN-_vQvNujAPaTH1Ps0A)](https://www.youtube.com/watch?v=SiMvXK41jdM)

![image](https://github.com/user-attachments/assets/85daba7c-45d1-447e-9054-a8677ef9f2b1)



With this script you connect a kazoo webhook to it and then will get connected to couchdb and parse variables with postgresql queries to be executed on fusionpbx's environment.

Note that you should configure fusionpbx with provisioner settings enabled and configure opentelecom's provisioner for visualize the phones JSON on SmartPBX

### Steps:

1. Install a fusionpbx  normally on different instance or VPS <br>
2. Deploy and follow Opentelecom's provisioner until the section "Create phone make, family and model details" Note that postgresql client must be installed with curl, python3 and PHP 5 or 7.2 <br>
3. Clone the latest fusionpbx code from github and also change the directory to FUSIONPBX-WEBROOT/resources/templates/provision Check 'script-otf-api-phones.php' and execute it under FUSIONPBX-WEBROOT/resources/templates/provision'
4. The returned output will give a set of links to be executed instead of the OpenTelecom's section (as per, step 2).  Note that some links should still be adjusted to be able to add more models <br> <br>
5. Follow the instructions like "Test the provisioner" and skip the crossbar.devices and the rest of the page <br>
6. Inside the env.php in webhook folder; configure the Master account user's md5sum, CouchdB and postgresql connection parameters and ensure they are correct. In fusionpbx; edit postgresql.conf and pg_hba.conf to allow connections from the server where the script is located, change listen address to eth0 server's IP and add another line in pg_hba.conf, similar to 127.0.0.1 but allowing connections from where webhook server is located   <br>
7. Go to Master Account and configure webhooks (about 6 of them).<br>

   webhook A:<br>
   -Trigger Event = Object <br>
   -Request Type = POST <br>
   -URL: The prov-webhook php URL <br>
   -Body Format: JSON <br>
   -Custom Data >> Type: account >> Action: doc_created <br>
   <br>
   
   webhook B: <br>
   -Trigger Event = Object <br>
   -Request Type = POST  <br>
   -URL: The prov-webhook php URL <br>
   -Body Format: JSON  <br>
   -Custom Data >> Type: account >> Action: doc_edited  <br>
   <br>
   
   webhook C: <br>
   -Trigger Event = Object <br>
   -Request Type = POST  <br>
   -URL: The prov-webhook php URL <br>
   -Body Format: JSON  <br>
   -Custom Data >> Type: device >> Action: doc_created <br>
   <br>
   
   webhook D: <br>
   -Trigger Event = Object <br>
   -Request Type = POST <br>
   -URL: The prov-webhook php URL <br>
   -Body Format: JSON <br>
   -Custom Data >> Type: device >> Action: doc_edited <br>
   <br>

   webhook E: <br>
   -Trigger Event = Object <br>
   -Request Type = POST <br>
   -URL: The prov-webhook php URL <br>
   -Body Format: JSON <br>
   -Custom Data >> Type: device >> Action: doc_deleted <br>
   <br>

   webhook F: <br>
   -Trigger Event = Object <br>
   -Request Type = POST <br>
   -URL: The prov-webhook php URL <br>
   -Body Format: JSON <br>
   -Custom Data >> Type: account >> Action: doc_deleted <br>
   <br>

8. Enable the checkbox named 'Include Sub Accounts' to all of them. <br>
9. Now check everything by adding or editing account and phones on each of the tenants; if they are ok; head to fusionbpx webpanel and you will see the kazoo accounts and devices added with them. <br>
10. Replace SmartPBX App with the one listed in this project (https://github.com/fmateo05/monster-ui-voip-bin)
11. Kazoo SmartPBX includes combo_keys and feature_keys and they have about 4 key types: <br>
12. Combo/Feature/Expansion/Programmable Keys are able  to configure from kazoo to be replicated onto fusionpbx. Iterators can be added/edited on the aa_factory_defaults sections. <br>
<br>
<p> - speed dial <br>
    - parking <br>
    - personal parking <br>
    - presence (BLF) <br>
    - line <br>
</p>
<br>     
    Go to devices >> vendors >> Yealink (example) and put the values like the following: <br>
    'monitored call park' -> 10 <br>
    Repeat same addition for each brand for this entry. The idea is create a kind of duplicate but with the key type changed as above <br>
   <br>
13. Clone and Import FusionPBX provisioner App (https://github.com/fmateo05/monster-ui-fusionpbx-provisioner) as 'provisioner' inside apps folder
14. Once imported, please open the app and click the button to show up the URLs to be configured and added into phone devices (also with DHCP Option 66)
   If they show empty, please go to the required account using smartpbx and add a device of any type; then go back to provisioner app.

