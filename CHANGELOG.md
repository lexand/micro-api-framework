#0.0.29-alpha
- default module controller (accessible via /)
- default controller action (index) (accessible via URL path /\[module/\]controller)
- tests for modules/default controller/default actions

#0.0.26-alpha
- added DefaultResponseFactory. Should minimize calling of the clone() in \Psr\Http\Message\ResponseInterface::with* methods
- added examples for microapi-framework usage in tests/functional/router.php 
- added sending Response to the client
- event handlers now can be inserted before or after previous

#0.0.23-alpha
- PSR-7 integration completed (but still need some improvements)

#0.0.21-alpha
- used PSR-7 

#0.0.16-alpha
- events (beforedispatch, afterdispatch, beforeaction, afteraction)
- endpoints cache builder for avoiding reflection on every request 
- accept RAW DTO in POST/GET and any other HTTP method
- all base functionality covered with unit tests