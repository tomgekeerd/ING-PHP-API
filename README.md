# ING-PHP-API

Please don't mind the mess or the unorganized names, I've put this together as an experiment for myself but thought it would be nice to open-source since the purpose for which I've wanted this use it for turned out to be impossible.

Anyways, this is an ING API PHP Wrapper. It basically wraps this ING API in a PHP script using the Slim framework so you can call the urls much easier and can get some data in a more efficient way. To see a demo of using this API in a real life experience, head over to my '[ING-Banking](https://github.com/tomgekeerd/ING-Banking)' app where I use it in some iOS Swift Application.

# Security

As said, this was purely for experiment purposes, so security wasn't a high priority here. You'll send your username + password unencrypted over a URL, so for that, I recommend using HTTPS if you would like to try it out on any server whatshowever. Further details are that all the data will be returned directly from ING itself without any other partner in between & logging in is simply done by entering the form on mijn.ing.nl. 

# Be aware

Because this script simply logs in to the form on the website, it is still possibly to block your account, so be aware that when your password is wrong for over ~10 times, you'll have to get a new password. **That this will happen is not returned with this wrapper**.