# Symfony Messenger & API Platform integration

Using a message bus like [Symfony Messenger](https://symfony.com/doc/current/messenger.html) is a wonderful way of structuring
your application around commands or queries (which will _just_ be PHP classes). [API Platform](https://api-platform.com/) is
a great framework to expose APIs.

The point of this bridge is to enable you to build business actions-centric APIs instead of CRUD APIs. [Check this very simple example](https://github.com/sroze/api-platform-messenger-example).

**Note:** This is still an experimentation. You will likely have to contribute to make it fit your needs. Looking forward to review your pull-requests!

## Usage

1. Get an API Platform application. Easiest is to use Symfony's `api` pack:
   ```bash
   composer create-project symfony/skeleton api-platform-and-messenger && \
   cd api-platform-and-messenger && \
   composer req api
   ```

2. Install this bridge
   ```bash
   composer req sroze/api-platform-messenger:dev-master
   ```

3. Configure your message(s) to be handled by API Platform like in the following example:
   ```php
   <?php
   
   namespace App\Message;
   
   use Sam\ApiPlatform\Messenger\Annotation\ApiMessage;
   use Symfony\Component\Validator\Constraints\NotBlank;
   
   /**
    * @ApiMessage(
    *   path="/write-message",
    *   type="command"
    * )
    */
   class WriteMessage
   {
       /**
        * @NotBlank
        *
        * @var string
        */
       public $message;
   }
   ```

## Reference

### `@ApiMessage` annotation

- `path`. The URL path where your command will be exposed.
- `type`. The type of message. Can be:
   - `query`: Will be exposed via a `GET` method
   - `command`: Will be exposed via a `POST` method
