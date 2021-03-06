# PushBackend - Push Notification Service for Mobile Applications

PushBackend is a PHP Codeigniter and Amazon SNS based mobile push notification project. It is a base service of sending push message to all mobile clients, specific user or topic. It includes base and sample functions, you can add your functions and implement your scenerios.

The project uses Codeigniter, MySQL but you can easily use it without Codeigniter framework. 

- Amazon SNS 
- iOS, Android, Windows Phone and others
- iOS Development, Distribution management
- JSON, XML etc. formats supported (Detail:  [CodeIgniter Rest Server])
- Topic, User management
- Send image, sound etc with push message


### Version
1.0


### Future Tasks

- Node.js and MongoDB support.
- iOS and Android Demos
- Optional push message service. Amazon SNS, GCM, APNS etc.


### Tech

PushBackend project is a PHP project. It uses Amazon SNS for push notification service.

The project is developed with Codeigniter but it can be use without framework.

PushBackend uses some open source projects to work properly:

* [AmazonSNS-PHP-API] - A lightweight PHP wrapper for the Amazon SNS API
* [CodeIgniter Rest Server] - A fully RESTful server implementation for CodeIgniter using one library, one config file and one controller.

### Flow of token adding, updating and removing

![TokenFlow](http://iravul.com/push/upload/TokenGeneration.png)

### Usage

1- Add Access Key, Client Secret, Region to config/site_settings.php. How to get AWS Credentials: http://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSGettingStartedGuide/AWSCredentials.html
```php
$config['aws_accesskey']        = 'XXXXXXXX';
$config['aws_secretkey']        = 'XXXXXXXX';
$config['aws_region']           = 'eu-west-1';
```

2- Add your application ARN's to config/site_settings.php. How to get Application ARN from Amazon SNS: http://docs.aws.amazon.com/sns/latest/dg/mobile-push-send-register.html
```php
$config['push_arn_android']     = '';
$config['push_arn_ios_pro']     = '';
$config['push_arn_ios_dev']     = '';
```

3- Create related tables from push-db.sql

4- Enable authentication and logging from config/rest.php (optional)

5- Adding token (POST method, "iravul/1/setToken")

| Parameter        | Description           | 
| ------------- |:-------------:|
|user_id   | user identifier of your project |  
|token   | Apple device token, GCM token etc.  |  
|token_type   | development, production  | 
|device   | optional  | 
|device_id   | optional for iOS, mandatory for Android  | 
|osType   | ios, android  |  
|osVersion   |  optional  |   
|app_version   | optional  |  



6- Removing token (POST method, "iravul/1/removeToken")

| Parameter        | Description           | 
| ------------- |:-------------:|
|user_id   | user identifier of your project |  
|token   | Apple device token, GCM token etc.  | 



7- Send push message (POST method, "iravul/1/push")

| Parameter        | Description           | 
| ------------- |:-------------:|
|user_id   | user identifier of your project |  
|message   | should be max 200 char because of push message packet size limit  |  
|type   | will be used for differentiate push type. you can send general message, image, sound or pool push messages. it will be differed by $type attribute.  |  
|type_id   | you can get push detail with type_id and type values. then you make a request and get data.  |  

License
----
Copyright 2016 Ergin Kucukiravul

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.





   [AmazonSNS-PHP-API]: <https://github.com/chrisbarr/AmazonSNS-PHP-API>
   [CodeIgniter Rest Server]: <https://github.com/philsturgeon/codeigniter-restserver>
