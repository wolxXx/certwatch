certwatch
===
- get information about certificates for domains
- the days and date the certificate is valid
- the issuer of the certificate

- requirements
  - php >= version 7.1

- first step
  - run `php composer.phar install` for fetching the needed packages 

- manual installation:
  - create a file called "domains.txt", you may just copy the domains.template.txt to domains.txt and remove the example domains
  - fill in your domains you want to be watched

- guided installation
  - run `php run.php --init`
  - it creates a mail-config.php you will need to adjust if you want to have the results via mail or delete it if you do not want to have mails
  - it creates a domains.txt file with example domains
  - fill in your domains you want to be watched

- run the watcher
  - run `php run.php` in a terminal session and have a look at the table..
  - there are some files generated for you
    - results.html: a simple html page with the results
    - results.json: the results in json format
    - results.xml: the results in xml format

- options to pass to the runner
  - `--help`: display the help message
  - `--init` initialize the runner
  - `--check`: ignore the domains.txt file and check certificate status for the given domains, separated by comma
    - example: `php run.php --check=github.com,facebook.com,google.com`
  - `--skip-console`: do not display results in the console / terminal
  - `--skip-files`: do not generate any files (xml, json,...)
  - `--skip-html`: do not generate the html representational file
  - `--skip-json`: do not generate the results.json file
  - `--skip-mail`: do not send results by mail
  - `--skip-xml`: do not generate the results.xml file
  - by default, all domains in the domains.txt file are checked and xml, json and html are generated and an email will be sent 

- customization
  - there is a file called results.default.twig that is used to generate the results.html file
  - you may want to have another style, format, so just copy the results.default.twig file to results.twig and customize it the way you want
  

upcoming features
===

- send e-mail if a domain runs out of validity
- linux daemon script
- executable phar archive
- "only list domains that are running out of validity" option
- grouping domains
