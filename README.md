# SYMFONY FRONTEND ROLE PERMISSION BUNDLE

A Symfony frontend role permission bundle. This bundle allow site administrator to manage user roles and permissions.

## INSTALLATION

Before installing this package, copy this configuration in config/packages/symfrop.yaml file in your symfony project

```yaml
symfrop_bundle:

  resources_info:
    App/Controller:  '%kernel.project_dir%/src/Controller'

  entities:
    entity:
      user_entity: 'Njeaner\Symfrop\Entity\User'
      role_entity: 'Njeaner\Symfrop\Entity\Role'
      action_entity: 'Njeaner\Symfrop\Entity\Action'
    form: 
      role_form: 'Njeaner\Symfrop\Form\RoleType'
      action_form: 'Njeaner\Symfrop\Form\ActionType'
      user_role_form: 'Njeaner\Symfrop\Form\UserRoleType'

  app_roles:
    ROLE_USER: 'user'
    ROLE_ADMIN: 'admin'
    ROLE_SUPERADMIN: ['root', false]
```

When the config file is copied in config/packages/symfrop.yaml, then install the package with the following command:

```php
composer require  njeaner/symfrop
```

After package installation, copy this code in config/routes/attributes.yaml

```yaml
controllers:
   resource: '@Symfrop\Controller\'
   type: attribute
```

 And generate Symfrop User, Role and Action entities with this command:

```php
php bin\console symfrop:entities [entities-folder]
```

or this command:

```php
symfony console symfrop:entities [entities-folder]
```

The optional [entities-folder] argument is the folder to contains your auth entities. if this argument is missed, the entities will be generated in src/Entity/ directory.
for example, "php bin\console symfrop:entities Auth" command will generate symfrop entities in src/Entity/Auth/ directory.

now in your config/packages/symfrop.yaml, changes this lines:

```yaml
    user_entity: 'Njeaner\Symfrop\Entity\User'
    role_entity: 'Njeaner\Symfrop\Entity\Role'
    action_entity: 'Njeaner\Symfrop\Entity\Action'
```

by this:

```yaml
    user_entity: 'App\Entity[\entities-folder]\User'
    role_entity: 'App\Entity[\entities-folder]\Role'
    action_entity: 'App\Entity[\entities-folder]\Action'
```

example:

```yaml
    user_entity: 'App\Entity\Auth\User'
    role_entity: 'App\Entity\Auth\Role'
    action_entity: 'App\Entity\Auth\Action'
```

or if entities is generated directly in src/Entity directory

```yaml
    user_entity: 'App\Entity\User'
    role_entity: 'App\Entity\Role'
    action_entity: 'App\Entity\Action'
```

NB: Your are not obligate to use symfrop command to generate entities, this command is an simple way to generate easily all needed entities. symfrop bundle use three central entities:

- an user entity which represente application users, this entity must implements **Njeaner\Symfrop\Entity\Contract\UserInterface** that extends symfony user interface;
- an role entity that define different type of role to use in the application, this entity must implements **Njeaner\Symfrop\Entity\Contract\RoleInterface**. Default roles are:
    1. ROLE_USER (a simple user role),
    2. ROLE_ADMIN (an administrator role),
    3. ROLE_SUPERADMIN (root role with all previlegies).
- an action entity that define all users actions in the application, this entity must implements **Njeaner\Symfrop\Entity\Contract\ActionInterface**. Each symfony controller action is an symfrop action that can be specified using symfrop class and methods attributes.

## HOW TO USE IT?

### **Defining symfrop user action**
Symfrop bundle use controller class and method attributes to define users actions and permissions in the application. Two attributes can be used to define a controller action:

- **Njeaner\Symfrop\Core\Annotation\Route** which is an extension of Symfony\Component\Routing\Annotation\Route with some added properties for symfrop bundle. when using this attribute, it not necessary to use more symfony routing attribute, because it accept all symfony routing attribute properties plus symfrop attribute properties.
- **Njeaner\Symfrop\Core\Annotation\RouteAction**. this attribute must be combine with symfony routing attribute. thus, any symfony controller action must contains two methods attributes instances: a Symfony\Component\Routing\Annotation\Route to define symfony routing and Njeaner\Symfrop\Core\Annotation\RouteAction to define symfrop user action.

symfrop attribute properties are:

- **name(required)**: to define the action name
- **title(optionale)**: to define action title, if it value is missing, name value will be used
- target(optional): string or array value to define role associated to an defined action.
- isUpdatable(default: true): define if and action is updatable or no.
- hasAuth(default: true): define if an controller action is authenticable by symfrop bundle or no. each controller action function that do not get symfrop attribute is not authenticable by the bundle.
- isIndex(default: false): to specify if the action is and index action
- updatedRole(default: false): this is use when attribute target value is modified. it permit to update database role correspondances 
- isUpdated(default: false): this is use when (an) attribute(s) property value is changed. this allow to update database values.
- actionCondition(default: null): supplementary condition to check during authorization checking
- conditionOption: use with actionCondition property to specify if the actionCondition will overwrite authorization checking, or wil be combined with it.
Njeaner\Symfrop\Core\Annotation\RouteAction attribute accept only these properties. Njeaner\Symfrop\Core\Annotation\Route however combine these properties with Symfony\Component\Routing\Annotation\Route attribute properties to define at time, symfony routing action and symfrop user action.
 an simple example:

```php
namespace App\Controller\Auth;

use App\Repository\Auth\UserRepository;
use Njeaner\Symfrop\Core\Annotation\Route ;
use Njeaner\Symfrop\Core\Service\CONSTANTS;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/auth/user/{_locale<fr|en|es|pt>?en}', requirements: ['_locale' => 'fr|en|es'])]
class UserController extends AbstractController
{
    // path, name, methods are for symfony routing. name, target, isIndex are for symfrop user action.
    #[Route(path: '', name: 'app_auth_user_index', methods: ['GET'], target: CONSTANTS::ROLE_ALL, isIndex: true)]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('auth/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
}
```

### **Checking symfrop user action**

The symfrop authorization are checking in a kernel.request event and before controller instanciation. the bundle use current route name and current security user to chech action authorization.

### **Checking in a controller**

Sometimes we can need to check in a controller action if some route is authorized or no.

```php
//...
use Njeaner\Symfrop\Core\Manager\AnnotationManager;
class UserController extends BaseAppController
{
    // path, name, methods are for symfony routing. name, target, isIndex are for symfrop user action.
    #[Route(path: '', name: 'app_auth_user_index', methods: ['GET'], target: CONSTANTS::ROLE_ALL, isIndex: true)]
    public function index(UserRepository $userRepository, AnnotationManager $am): Response
    {
        if($am->isAuthorize('some-route-name')){ // return true or false
            // do some action;
        }
        return $this->render('auth/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
}
```

### **Cheking in twig template**

Check user action authorization in twig template allow to show or hide a link, a form depending on user action permission. It allow for example to show user edit action link or form only for users that get permissions to edit users informations.

Symfrop bundle provide three way to check user action authorization in twig template:

- **create_symfrop_link** function that permit to create a link. with this twig function, the link is generate only if current user has action permission to process this link. it accept between 2 and 4 arguments:
    1. The Required first argument (of type string) is the route name.
    2. The Required second argument (of type string or array of two string) is label of the link. if is a array argument, the first array item will be link label, the second array item will be the text to show if the link is not generated.
    3. The Optional third argument is parameters to generate link href
    4. The Optional last argument is option to add some html tag attributes to the link, like class, id, title , style, ...

    EXAMPLE:

```twig
    {{ create_symfrop_link(
        'my_route_name',
        'link_label', {# or ['link_label', 'default_label'] #}
        {'param': 'param_value'},
        {'class': 'btn btn-primary', 'title': 'link_title'}
    ) }}    
```

- **symfrop** tag that permit to render a block only if user get an action permission. This is usefull to render a form only if user get permission to process form action. For , show edit user information form for only admin that have permission to edit user information. **symfrop** tag have an **else** tag that permit to render other thing if user no get permission to process the given action

EXAMPLE

```twig
{% symfrop route_name %}
 {# show some block #}
{% endsymfrop %}

{# or #}

{% symfrop route_name %}
{# show some block #}
{% else %}
{# show other block  #}
{% endsymfrop %}
```

- **is action autorized** if tag that permit to check if a user get an action route permission

EXAMPLE

```twig
{% if route_name is authorized action %}
 {# show some block #}
{% endsymfrop %}

{# or #}

{% symfrop route_name %}
{# show some block #}
{% else %}
{# show other block  #}
{% endsymfrop %}
```

### **translation in Symfrop bundle**

Symfrop bundle provide some twig function to process translation:

- **__** function that is symfrop basic locale translation function
- **__u** or **__U** functions that is ucfirst locale translation function
- **__t** or **__T** that is a derivation of symfony **title** filter.

USAGE EXAMPLES

```twig
    __('name') {# will return: "name" if request locale is english(en), "nom" if french(fr) or "nombre" if request locale is spanish(es)  #}
```

### **Generate Entity Crud**

symfrop bundle provide a symfony equivalent make:crud command, to easily make a crud that generate controller with Njeaner\Symfrop\Annotations\RouteAction method attribute, form that include locale translation and view with symfrop action link and form permission checking.

Generate symfrop crud using php command

```php
php bin/console symfrop:crud
```