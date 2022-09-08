<?php
namespace Maxim\Postsystem\UnitTests\Actions\LikeActions;

use Maxim\Postsystem\Blog\Like;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\LikeAlreadyExist;
use Maxim\Postsystem\Exceptions\RepositoriesExceptions\LikeNotFound;
use Maxim\Postsystem\Http\Actions\LikeActions\LikeCreate;
use Maxim\Postsystem\Http\ErrorResponse;
use Maxim\Postsystem\Http\Request;
use Maxim\Postsystem\Http\SuccessfulResponse;
use Maxim\Postsystem\Repositories\LikeRepositories\ILikeRepository;
use Maxim\Postsystem\UUID;
use PHPUnit\Framework\TestCase;

class LikeCreateTest extends TestCase
{

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnSuccessfulResponse() :void
    {
        $request = new Request([],[],'{"post_uuid":"351739ab-fc33-49ae-a62d-b606b7038c87","author_uuid":"2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"}');

        $likeRepositoryStub = $this->likeRepository([]);

        //Создаем действие
        $action = new LikeCreate($likeRepositoryStub);
        //Выполняем действие
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);

        $this->setOutputCallback(function ($data){
            $dataDecode = json_decode(
                $data,
                associative: true,
                flags: JSON_THROW_ON_ERROR);

            $dataDecode["data"]["uuid"] = "fb40d053-026c-4e64-83fe-0d9882cd3464";

            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        $this->expectOutputString('{"success":true,"data":{"uuid":"fb40d053-026c-4e64-83fe-0d9882cd3464"}}');


        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnErrorResponseIfLikeAlreadyExist() :void
    {
        $request = new Request([],[],'{"post_uuid":"351739ab-fc33-49ae-a62d-b606b7038c87","author_uuid":"2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"}');

        $likeRepositoryStub = $this->likeRepository([
            new Like(
                new UUID("fb40d053-026c-4e64-83fe-0d9882cd3464"),
                new UUID("351739ab-fc33-49ae-a62d-b606b7038c87"),
                new UUID("2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa")
                )
            ]);

        //Создаем действие
        $action = new LikeCreate($likeRepositoryStub);
        //Выполняем действие
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);


        $this->expectOutputString('{"success":false,"reason":"Like already exist."}');


        $response->send();
    }

     /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnErrorResponseIfUuidMailformed() :void
    {
        $request = new Request([],[],'{"post_uuid":"351739ab-fc33-49ae","author_uuid":"2a5f9ba6-b0c2-4143-9ca0-486ca286ebaa"}');

        $likeRepositoryStub = $this->likeRepository([]);

        //Создаем действие
        $action = new LikeCreate($likeRepositoryStub);
        //Выполняем действие
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);


        $this->expectOutputString('{"success":false,"reason":"Malformed UUID: 351739ab-fc33-49ae"}');


        $response->send();
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnErrorResponseIfTransferedNotFullData() :void
    {
        $request = new Request([],[],'{"post_uuid":"351739ab-fc33-49ae-a62d-b606b7038c87"}');

        $likeRepositoryStub = $this->likeRepository([]);

        //Создаем действие
        $action = new LikeCreate($likeRepositoryStub);
        //Выполняем действие
        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);


        $this->expectOutputString('{"success":false,"reason":"No such field: author_uuid"}');


        $response->send();
    }



    private function likeRepository(array $likes) :object
    {
        return new class($likes) implements ILikeRepository{

            private array $likes;
            public function __construct(array $likes)
            {
                $this->likes = $likes;
            }


            public function save(Like $like): void
            {
                foreach($this->likes as $likeObj){
                    //Проверяем на единичность лайка в репозитории
                    if((string)$likeObj->getAuthorUuid() === (string)$like->getAuthorUuid() && 
                        (string)$likeObj->getPostUuid() === (string)$like->getPostUuid()){
                        throw new LikeAlreadyExist("Like already exist.");
                    }
                }
            }

            public function getByPost(UUID $post): array
            {
                throw new LikeNotFound("Not found");
            }

            public function getByUUID(UUID $uuid): Like
            {
                throw new LikeNotFound("Not found");
            }

            public function delete(UUID $uuid): void
            {
                
            }
        };
    }
}