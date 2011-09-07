<!--

  LGI user configuration file.

  !IMPORTANT! This file contains your private access key. Please
          do not distribute this file and make sure it is not
          readable by others.

  Note that the certificate must appear before ca_chain,
  and that the certificate and key blobs should not be indented.

-->
<LGI_user_config>
  <defaultserver>{$lgi.server}</defaultserver>
  <defaultproject>{$lgi.project}</defaultproject>
  <defaultapplication>{$application}</defaultapplication>
  <user>{$lgi.user}</user>
  <groups>{$groups}</groups>
  <certificate>
{$certificate}
  </certificate>
  <privatekey>
{$privatekey}
  </privatekey>
  <ca_chain>
{$ca_chain}
  </ca_chain>
</LGI_user_config>
