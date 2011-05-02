# Phenix Framework

Phenix is a mini RESTful MVC framework on top of php-rack inspired by [Rails](http://rubyonrails.org/).

php-rack was developed by [Jim Myhrberg](https://github.com/jimeh) and is currently maintained by [Ted Kulp](https://github.com/tedkulp)

## Features

* Simple MVC architecture
* RESTful HTTP routes (GET, POST, PUT, DELETE)
* Built on a library similar to [Rack](http://rack.rubyforge.org/) for easy middleware-based expandability
* Database support with [Idiorm](https://github.com/j4mie/idiorm) and [Paris](https://github.com/j4mie/paris) for Active Record
* Template system that allows custom views (ie. [Twig](http://www.twig-project.org/), [Smarty](http://www.smarty.net/), ... Check the [Phenix-Extra](https://github.com/fredi/Phenix-Extras) repository for already made custom views)
* Flash messaging
* Error handling (show details only locally)
* Logging system
* Simple command line script to generate controllers and models
* Supports PHP 5.1.2+

### Coming soon

* Caching system
* Unit testing w/ [PHPUnit](https://github.com/sebastianbergmann/phpunit/)

## Getting Started

### Installing Phenix

1. Clone the Phenix repository

        git clone git://github.com/fredi/Phenix.git

2. Change directory to Phenix, init and update submodules in the repository (php-rack, idiorm and paris)

        cd Phenix
        git submodules init
        git submodules update

3. Create a symbolic link of the public directory to your public_html or www:

        ln -s /home/user/Phenix/public /home/user/public_html

    You can create a Virtual host too, but don't forget do set the DocumentRoot to the public directory.

4. Restart your server if needed and go to the url you just created and you'll see:

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

    Now that you created the controller, the action inside it and the view of the action, let's create a route so the Phenix Framework can call it.

4. Create a file called 'routes.php' inside the '/config' directory with the following code:

        <?php
        Phenix::get('/', 'hello#index'); // Call the index action in the hello controller

    In this case, where you want to call the 'index' action you could also specify just the controller, because 'index' is the default action.

5. Now you are done! Just access the address you're serving the application and it should be rendering 'Hello World!'.

### More on Routes

You can create routes using parameters, for example:

    Phenix::get('/:controller/:action');
    Phenix::get('/:controller');

This routes are using parameters, and Phenix will know what to do if you access, lets say 'http://localhost/user/list'. It will call the 'list' action in the 'user' controller.

You can pass Regex conditions to the parameters of your routes too, like:

    Phenix::get('/:controller/:action/:id)->conditions(array('id' => '\d{1,8}'));

It will accept an id with just digits (max. 8 digits). If we try to access 'http://localhost/user/show/abc' it will not execute that route, because 'abc' isn't numeric.

You can wrap all routes above in one using conditional parameters:

    Phenix::get('/:controller(/:action(/:id))')->conditions(array('id' => '\d{1,8}'));

  Note that I'm using the ':action' and ':id' parameters inside parathesis.

### RESTful Routes

One nice thing you can do with RESTful Routes is to use just one URL but execute different actions depending on the request method:

    Phenix::get('/products', 'products#list');
    Phenix::post('/products', 'products#save');
    Phenix::put('/products', 'products#update');
    Phenix::delete('/products', 'products#destroy');

That's very nice!

### Making a "Hello World" Rack Middleware

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
        require_once ROOT.DS."lib".DS."Phenix".DS."Phenix.php";
        
        Rack::add("HelloWorld", ROOT.DS."lib".DS."helloworld.php");
        Rack::add("ExceptionHandler", MIDDLEWARE_PATH.DS."ExceptionHandler.php");
        Rack::add("MethodOverride", MIDDLEWARE_PATH.DS."MethodOverride.php");
        Rack::add("HeadRequest", MIDDLEWARE_PATH.DS."HeadRequest.php");
        Rack::add("Phenix", null, Phenix::getInstance());

        Rack::run();

3. Access the '/hello_world' URL and you should get the response from your Rack Helloworld application, without even executing another middleware in the stack. So if you want to perform a simple action and respond to the user, you could create your own Rack middleware, use it and spare some milliseconds. Cool, isn't it?

## Resources

You can find additional resources in the Phenix-Extras repository, like custom views (to render using Twig, Smarty or another template engine).

<https://github.com/fredi/Phenix-Extras>

## Thanks

Some of the code is based on [silk](https://github.com/tedkulp/silk) by Ted Kulp and [Slim](https://github.com/codeguy/Slim) by Josh Lockhart. Thanks!

## License

Phenix is released under the MIT license.