# Email

[![Build Status](https://travis-ci.org/gourmet/email.svg?branch=master)](https://travis-ci.org/gourmet/email)
[![Total Downloads](https://poser.pugx.org/gourmet/email/downloads.svg)](https://packagist.org/packages/gourmet/email)
[![License](https://poser.pugx.org/gourmet/email/license.svg)](https://packagist.org/packages/gourmet/email)

Makes [CakePHP 4]'s `Email` even better by adding:

* Default email layout with basic styling (safe for emails)
* `EmailHelper` which extends `HtmlHelper` to automatically format paragraphs, links, etc.

## Install

Using [Composer]:

```
composer require lopezlean/cakephp-gourmet-email:dev-master
```

You then need to load the plugin. In `Application.php`, something like:

```php
$this->addPlugin('Gourmet/Email');
```

## Usage

In your email views, you can now use the `Gourmet/Email.Email` helper:

```php
// app/Template/Email/html/welcome.ctp
Welcome <?= $user['username'] ?>

Please confirm your account by clicking on the link below:

<?= $this->Email->link('Confirm account', '/') ?>

If for any reason, you are unable to click the link above, copy/paste the following to your browser's address bar:

<?= \Cake\Routing\Router::url('/') ?>

Thank you for choosing us,

Company name

<?= $this->Email->image('logo.jpg') ?>
```

For the `text` version, you only need to symlink the same template. We'll symlink all `text` templates to `html`:

```
$ ln -s html app/Template/Email/text
```

It's finally ready to send an email:

```php
        $mailer
            ->setEmailFormat('both')
            ->setTo("to@example.com")
            ->setFrom(['example@example.com' => 'Example'])
            ->viewBuilder()
                ->setHelpers(['Html','Gourmet/Email.Email'])
                ->setLayout('Gourmet/Email.default')
                ->setTemplate('my_template');
```

## Patches & Features

* Fork
* Mod, fix
* Test - this is important, so it's not unintentionally broken
* Commit - do not mess with license, todo, version, etc. (if you do change any, bump them into commits of
their own that I can ignore when I pull)
* Pull request - bonus point for topic branches

## Bugs & Feedback

http://github.com/lopezlean/cakephp-gourmet-email/issues

## License

Copyright (c) 2014, Jad Bitar and licensed under [The MIT License][mit].
Copyright (c) 2020, Leandro López and licensed under [The MIT License][mit].

[CakePHP 4]:http://cakephp.org
[Composer]:http://getcomposer.org
[mit]:http://www.opensource.org/licenses/mit-license.php
