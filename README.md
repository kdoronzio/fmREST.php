# fmREST.php

A free tool from soSIMPLE Software now updated for FileMaker 18.

Simplifies & manages PHP connections to FileMaker 17 and 18’s REST-based Data API.

NOTE: UPGRADED TO WORK WITH FILEMAKER 17 and FILEMAKER 18. NO LONGER WORKS WITH FILEMAKER 16.

We created this class file to make it easier to manage dynamic REST sessions for soSIMPLE and our custom development. The goal of the class file was to help PHP developers start using the new REST engine as quickly and easily as possible.

# What fmREST.php does:

- Makes every REST call available as a PHP function.
- Automatically login into FileMaker Server whenever you call any REST functions
- Saves your token for 15 minutes to reuse
- Checks for a broken or disconnected token and automatically reconnects and runs your function again

# Documentation:
Sample.php is a simple form that demonstrates most of the calls available by the fmREST.php class.

1. Follow the setup instructions on our site and FileMaker's site for the Data API (see link below)
2. Edit the sample.php file, entering your FileMaker Server address (should be the Fully Qualified Domain Name (FQDN) that your FMS is accessible at) at line 13: $host = your.server.address
3. Place the class file (fmREST.php) and the sample file (sample.php) in either (1) the https root of your FileMaker Server-hosted web server directory (See FileMaker Server docs for location) or (2) on any other php enabled web server that can connect to your FileMaker Server address. 
4. Upload the included sample.fmp12 file to your FileMaker Server 17. This file is a very basic FileMaker file that includes a user with FMREST security privileges. (note: the developer user name is "admin" and the password is "paradise").
5. Load sample.php in a web browser over https protocol. You should see a form that will allow you to do simple record modification along with all the API calls available from the FileMaker Data API. (The sample form will still work without ssl, but the tokens will not be saved between calls so you will see multiple REST connections on your FileMaker Server).

For complete documentation and support please visit out website:
http://www.sosimplesoftware.com/fmrest.php

We’ll also be updating it with new features. If you’d like to add something to it or have any comments, please let us know.

Copyright 2019 Paradise Partners, Inc DBA soSIMPLE Software / Ken d'Oronzio

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
