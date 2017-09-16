* run `composer install` to set up autoloader and dependencies
* set up server `php -S localhost:8080 -t ."


Then call the end points:
* [GET] /emoji/
* [GET] /emoji/{id}
* [POST] /emoji/

example post request with curl:
curl "http://localhost:8080/emoji/" -X POST -H "Content-Type: application/json"  -d '{"id": 10, "code": "U+1F4A9", "value": "💩", "description": "pile of poo", "hasSkinTone": false}'

example failing request:
curl "http://localhost:8080/emoji/" -X POST -H "Content-Type: application/json"  -d '{"id": true, "code": "U12312", "value": true, "description": "", "hasSkinTone": "true"}'
