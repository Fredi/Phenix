# Phoenix Framework

Phoenix is a mini RESTful MVC framework on top of php-rack inspired by [Rails](http://rubyonrails.org/).

php-rack was developed by [Jim Myhrberg](https://github.com/jimeh) and is currently maintained by [Ted Kulp](https://github.com/tedkulp)

## Features

* Simple MVC architecture
* RESTful HTTP routes (GET, POST, PUT, DELETE)
* Built on a library similar to [Rack](http://rack.rubyforge.org/) for easy middleware-based expandability
* Error handling
* Supports PHP 5+

### Coming soon

* Hability to integrate with ORM classes like [Idiorm](https://github.com/j4mie/idiorm) or [Doctrine](http://www.doctrine-project.org/)
* Some kind of extension system
* Integration with template engines like [Twig](http://www.twig-project.org/) or [Smarty](http://www.smarty.net/)
* Unit testing w/ [PHPUnit](https://github.com/sebastianbergmann/phpunit/)
* Set development mode to show error details
* Flash messaging
* Caching system
* Logging system
* Command line system to generate controllers/views and models

## Getting Started

### Installing Phoenix

1. Download Phoenix and extract the downloaded file to your user's home directory or another without public web access.
2. Create a symbolic link of the public directory to your public_html or www:

        ln -s /home/user/Phoenix/public /home/user/public_html

    You can create a Virtual host too, but don't forget do set the DocumentRoot to the public directory.

2. Restart your server if needed and go to the url you just created and you'll see:

        "The page you were looking for doesn't exist."

  It's just because there is no routes configured.

### Creating the "Hello World" Application

1. Create a file called 'hello_controller.php' in the 'app/controllers' directory with the following code (note: you really doesn't need to end the file with '?>'):

        <?php
        class HelloController extends ApplicationController
        {
            public function index()
            {
                // Set the variable hello to use in our view
                $this->set('hello', 'Hello World!');
            }
        }

2. Create a 'hello' directory inside 'app/views'.
3. Create a file called 'index.phtml' in the 'app/views/hello' directory with the following code:

        <p><?= $hello ?></p>

    Now that you created the controller, the action inside it and the view of the action, let's create a route so the Phoenix Framework can call it.

4. Create a file called 'routes.php' inside the '/config' directory with the following code:

        <?php
        Phoenix::get('/', 'hello#index'); // Call the index action in the hello controller

    In this case, where you want to call the 'index' action you could also specify just the controller, because 'index' is the default action.

5. Now you are done! Just access the address you're serving the application and it should be rendering 'Hello World!'.

### More on Routes

You can create routes using parameters, for example:

    Phoenix::get('/:controller/:action');
    Phoenix::get('/:controller');

This routes are using parameters, and Phoenix will know what to do if you access, lets say 'http://localhost/user/list'. It will call the 'list' action in the 'user' controller.

You can pass Regex conditions to the parameters of your routes too, like:

    Phoenix::get('/:controller/:action/:id)->conditions(array('id' => '\d{1,8}'));

It will accept an id with just digits (max. 8 digits). If we try to access 'http://localhost/user/show/abc' it will not execute that route, because 'abc' isn't numeric.

You can wrap all routes above in one using conditional parameters:

    Phoenix::get('/:controller(/:action(/:id))')->conditions(array('id' => '\d{1,8}'));

  Note that I'm using the ':action' and ':id' parameters inside parathesis.

### RESTful Routes

One nice thing you can do with RESTful Routes is to use just one URL but execute different actions depending on the request method:

    Phoenix::get('/products', 'products#list');
    Phoenix::post('/products', 'products#save');
    Phoenix::put('/products', 'products#update');
    Phoenix::delete('/products', 'products#destroy');

That's very nice!

### Making a "Hello World" Rack Application

Maybe you are asking yourself "what the hell is Rack?". It's basically an interface that sits between the HTTP request and our Application. [Check this out](https://github.com/tedkulp/php-rack#readme) for more information.

So, we will make a HelloWorld class that will return 'Hello World!' if we access the '/hello_world' URL.

1. First create a file called 'helloworld.php' in the '/lib' directory with this code:

        <?php
        class HelloWorld
        {
            function __construct(&$app)
            {
                $this->app =& $app;
            }
            function call(&$env)
            {
                // If we access the '/hello_world' URL it just returns the body 'Hello World!'
                if ($env['PATH_INFO'] == '/hello_world')
                    return array(200, array(), array('Hello World!'));
                // Otherwise we continue by calling the next application in the stack
                return $this->app->call($env);
            }
        }

2. Edit the 'config.ru' to add the HelloWorld application as the first Rack application in the stack. It should look like this:

        <?php
        // Load php-rack library
        require_once ROOT.DS."vendor".DS."php-rack".DS."lib".DS."Rack.php";
        
        // Load the framework
        require_once ROOT.DS."lib".DS."Phoenix".DS."Phoenix.php";
        
        Rack::add("HelloWorld", ROOT.DS."lib".DS."helloworld.php");
        Rack::add("ErrorPageHandler", PHOENIX_PATH.DS."middleware".DS."ErrorPageHandler.php");
        Rack::add("Phoenix", null, Phoenix::getInstance());

        Rack::run();

3. Access the '/hello_world' URL and you should get the response from your Rack Helloworld application, without event reaching to the ErrorPageHandler or the Phoenix Framework. Cool, isn't it?

## Thanks

Some of the code is based on [silk](https://github.com/tedkulp/silk) by Ted Kulp and [Slim](https://github.com/codeguy/Slim) by Josh Lockhart. Thanks!

## License

Phoenix is released under the MIT license.