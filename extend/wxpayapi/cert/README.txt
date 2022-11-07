Thank you for using WeChat Pay!
The attachment contains three files that are required for some high-security APIs: a certificate file in pkcs12 format, a certificate file in pem format, and a key file in pem format.
Certificates are sensitive information, which should be kept confidential and not be accessed by any unauthorized user.
The certificate format varies between different development languages, as described below:
    Certificate in pkcs12 format (apiclient_cert.p12)
        This is a certificate file in .p12 or .pfx format that is issued by WeChat Pay and contains a private key to identify and authenticate your identity.
        This certificate is required for the APIs requiring high security to verify your identity.
        On a Windows system, double-click this file to import it to the system. When prompted to enter the certificate password, enter your merchant ID (such as 1900006031) by default.
    Certificate in pem format (apiclient_cert.pem)
        This is a certificate file in .pem format that is exported from apiclient_cert.p12. Keep it secure to prevent leakage or any unauthorized access.
        This file is provided for some development languages or environments requiring .pem files, instead of .p12 ones.
        Alternatively, you can export it using the openssl command: openssl pkcs12 -clcerts -nokeys -in apiclient_cert.p12 -out apiclient_cert.pem.
    Certificate key in pem format (apiclient_key.pem)
        This is a key file in .pem format that is exported from apiclient_cert.p12. 
        This file is provided for some development languages or environments requiring .pem files, instead of .p12 ones.
        Alternatively, you can export it using the openssl command: openssl pkcs12 -nocerts -in apiclient_cert.p12 -out apiclient_key.pem.
Note:
        Most of the operating systems come with the root certificate of the certificate authority (CA) issuing the WeChat Pay server certificate. The CA certificate is no longer available for download as of March 6, 2018.