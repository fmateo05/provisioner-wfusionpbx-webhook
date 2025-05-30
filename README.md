### Kazoo Provisioner with FusionPBX Backend.


[![Demo Video](https://i.ytimg.com/an_webp/SiMvXK41jdM/mqdefault_6s.webp?du=3000&sqp=CLC6j8EG&rs=AOn4CLChCjDzRVUN-_vQvNujAPaTH1Ps0A)](https://www.youtube.com/watch?v=SiMvXK41jdM)

![image](https://github.com/user-attachments/assets/85daba7c-45d1-447e-9054-a8677ef9f2b1)



With this script you connect a kazoo webhook to it and then will get connected to couchdb and parse variables with postgresql queries to be executed on fusionpbx's environment.

Note that you should configure fusionpbx with provisioner settings enabled and configure opentelecom's provisioner for visualize the phones JSON on SmartPBX

### Steps:

1. Install a fusionpbx  normally on different instance or VPS <br>
2. Deploy and follow Opentelecom's provisioner until the section "Create phone make, family and model details" Note that postgresql client must be installed with curl, python3 and PHP 5 or 7.2 <br>
3. Check 'script-otf-api-phones.php' and execute it under FUSIONPBX-WEBROOT/resources/templates/provision' <br>
4. The returned output will give a set of links to be executed instead of the OpenTelecom's section (as per, step 2). <br>
5. Follow the instructions like "Test the provisioner" and skip the crossbar.devices and the rest of the page <br>
6. Go to fusionpbx's Menu >> Advanced >> Default settings >> provisioner section >> set 'enabled' with value 'true' >> grandstream_config_url to https://prov.example.com/app/provision/ <br>
7. In the same section; set 'http_auth_username' to 'phoneprov' , 'http_auth_passwod' to your desired password; also set 'http_domain_filter' to false <br>
8. Inside the webhook script; configure the CouchdB and postgresql connection parameters and ensure they are correct. In fusionpbx; edit postgresql.conf and pg_hba.conf to allow connections from the server where the script is located  <br>
 8 b) Create an user in master account and generate the md5 credentials for be added to API connection parameters. 
9. Go to Master Account and configure webhooks (about 6 of them).<br>

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

10. Enable the checkbox named 'Include Sub Accounts' to all of them. <br>
11. Now check everything by adding or editing account and phones on each of the tenants; if they are ok; head to fusionbpx webpanel and you will see the kazoo accounts and devices added with them. <br>
12. Combo/Feature Keys are able  to configure from kazoo to be replicated onto fusionpbx. Iterators can be added on the aa_factory_defaults sections. <br>
13. Kazoo SmartPBX includes combo_keys and feature_keys and they have about 4 key types: <br>
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

