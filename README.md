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

## USAGE

See here

tests/functional/router.php