# Codeigniter 4 (CI4) Login Register System

## What is in it?

In this system, there are functions to register, activate registration, login, 
forget password, reset password and filter url.

There is a time limit for making the forgotten password link expired.

This system can run well on localhost and I haven't tried it yet if the system runs well when it's hosted.

## Installation & updates

`composer create-project codeigniter4/appstarter` then `composer update` whenever
there is a new release of the framework.

When updating, check the release notes to see if there are any changes you might need to apply
to your `app` folder. The affected files can be copied or merged from
`vendor/codeigniter4/framework/app`.

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL
and any database settings.

Configure your database. This system uses a MySQL database. Table name: `tb_users`. Fields used:

`id_users` type `int(11), primary key, auto_increment`

`username` type `varchar(50)`

`email` type `varchar(50), UNIQUE`

`password` type `varchar(10)`

`mobile_number` type `varchar(15)`

`profile_pic` type `varchar(50)`

`created_at` type `datetime`

`status` type `varchar(20), As defined = inactive`

`uniq_id` type `varchar(32)`

`activation_date` type `datetime`

`forgpass_date` type `datetime`

Configure your `Email.php` file in the folder `app/Config/Email.php`. 

This configuration allows the system to send email to the destination email.

Change: 

`public $protocol` from `'mail'` to `'smtp'`

`public $SMTPHost` to `public $SMTPHost = 'smtp.gmail.com';` *in this case i use gmail*

`public $SMTPUser` to `public $SMTPUser = 'your@email.here';`

`public $SMTPPass` to `public $SMTPPass = 'yourPasswordEmailHere';`

`public $SMTPPort = 25;` to `public $SMTPPort = 465;`

`public $SMTPTimeout = 5;` to `public $SMTPTimeout = 60;`

`public $SMTPCrypto = 'tls';` to `public $SMTPCrypto = 'ssl';`

`public $mailType = 'text';` to `public $mailType = 'html';`

## Configure the Controllers
Open your folder `app/Controllers` and create new file `Auth.php`.
You can follow the code in the Auth.php file also Home.php file in the same folder.

`$uniqId = md5(str_shuffle('abc' . time()));` function to get random alphabets and characters to be used as user id.

In the register and check email functions there is a function to send email. Configure:

`$to = destination email;`

`$subject = subject email;`

`$token = get id user;`

`$message = what message would you send;`

`$this->email->setTo($to);`

`$this->email->setFrom('your email address, same email in Email.php file', 'Do Not Reply!!!');`

`$this->email->setSubject($subject);`

`$this->email->setMessage($message);`

`$this->email->send()`

## Configure the Models
Open your folder `app/Models` and create new file `ModelAuth.php`.
You can follow the code in the ModelAuth.php file

## Configure the Filters URL
Open your folder `app/Filters` and create new file `Auth.php` and `Home.php`.
You can follow the code in the Auth.php file also Home.php

After finished configuring, open your folder `app/Config/Filters.php`. 

In the function `public $aliases` 

after `'honeypot' => Honeypot::class,`, 

insert `'auth' => \App\Filters\Auth::class,` 

and `'home' => \App\Filters\Home::class,`.

Insert `'auth' => ['except' => ['auth/*', 'auth']]` 

in variable `'before => []'` 

at  function `public $globals` 

and `'home' => ['except' => ['auth/logout', 'home', 'home/*']]` 

in variable `'after => []'` at **the same function**.
