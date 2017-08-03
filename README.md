# micro-api-framework
API (or single page application) oriented micro framework

## GOAL
The main goal of this micro-framework is in the easiest way to transfer DTO (data transfer objects) via JSON (but can be
changed via \microapi\dto\DtoFactory) through HTTP methods to application and response DTO to client.
  
DTO idea was selected to solve validation problem of input data and simplification in documenting API methods.  

## NOTES
Some classes contains public fields. This approach
give more freedom and more performance? but at the same time it give more chances to
bring unpredictable chaos in you code. I assume that all this fields should be read-only
(almost every time). But if you really know what are you doing... or if you want to
shooting yourself in the foot, hand, eyes, head etc. you can freely modifier these fields.  

## Conventions
- each application module should be placed into separate namespace
- each module may has default controller, which accessible without controller name in URL path
- controllers in module should be placed into \\<module_ns>\controller namespace
- controllers of different modules must not intersects
- each controller may has default action. Default action method must has name "actionIndex"
- name of each controller action method should start from "action" prefix
- action method became accessible for Dispatcher if it is annotated with 
@methods(<list_of_allowed_comma_separated_http_methods>). For example
  - @methods(get) allows only GET HTTP method for concrete action method
  - @methods(get,post) allows GET and POST HTTP method for concrete action method

## Examples
### Conditions

```php
\microapi\Dispatcher::get()
                    ->addModule('admin', '\admin', main)
                    ->addDefaultModule('\app', 'main');
                    ...

namespace app\controller {
  class MainCtl extends \microapi\Controller {
    public function actionIndex(){
      
    }
  }
  class AdminCtl extends \microapi\Controller {
    public function actionIndex(){
      
    }
  }
  class ProfileCtl extends \microapi\Controller {
    public function actionIndex(){
      
    }
    public function actionUpdate(){
      
    }    
  }  
}

namespace app\admin\controller {
  class MainCtl extends \microapi\Controller {
    public function actionIndex(){
      
    }
  }
  class UserCtl extends \microapi\Controller {
    public function actionIndex(){
      
    }
    public function actionCreate(){
      
    }
  }
}

```
And now we have
| URL path| action method |
| --- | --- |
|/ | \app\controller\MainCtl::actionIndex()|
|/index | \app\controller\MainCtl::actionIndex()|
|/profile | \app\controller\ProfileCtl::actionIndex()|
|/profile/update | \app\controller\ProfileCtl::actionUpdate()|
|/admin | \app\admin\controller\MainCtl::actionIndex()|
|/admin/index | \app\admin\controller\MainCtl::actionIndex()|
|/admin/user | \app\admin\controller\UserCtl::actionIndex()|
|/admin/user/create | \app\admin\controller\UserCtl::actionCreate()|

## USAGE

See here

tests/functional/router.php