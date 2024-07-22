<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as OA;


#[Route('api/avis', name:'app_api_avis_')]
class AvisController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager, 
        private AvisRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {
    }
/**
     * @OA\Post(
     *     path="/api/avis",
     *     summary="Enregistrer avis",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Avis à enregistrer",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="pseudo", type="string", example="Pseudo"),
     *             @OA\Property(property="commentaire", type="string", example="Commentaire"),
     *             @OA\Property(property="isVisible", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Avis enregistre avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="pseudo", type="string", example="Pseudo"),
     *             @OA\Property(property="commentaire", type="string", example="Commentaire"),
     *             @OA\Property(property="isVisible", type="boolean", example=true)
     *         )
     *     )
     * )
     */

  #[Route(methods: 'POST')]
  public function new(Request $request): JsonResponse
  {
      $avis = $this->serializer->deserialize(
          $request->getContent(),
          Avis::class,
          'json'
      );
      

      // Tell Doctrine you want to (eventually) save the avis (no queries yet) 
      $this->manager->persist($avis);
      // Actually executes the queries (i.e. the INSERT query)
      $this->manager->flush();

      $responseData = $this->serializer->serialize($avis, 'json');
      $location = $this->urlGenerator->generate(
          'app_api_avis_show',
          ['id' => $avis->getId()],
          UrlGeneratorInterface::ABSOLUTE_URL
      );
      return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
  }

   /**
     * @OA\Get(
     *     path="/api/avis/{id}",
     *     summary="Get avis by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the avis"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avis details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="pseudo", type="string", example="Pseudo"),
     *             @OA\Property(property="commentaire", type="string", example="Commentaire"),
     *             @OA\Property(property="isVisible", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Avis not found"
     *     )
     * )
     */

   #[Route('/{id}', name: 'show', methods: 'GET')]
   public function show(int $id): JsonResponse
   {
       $avis = $this->repository->findOneBy(['id' => $id]);

       if (!$avis) {
           return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
       }

       $responseData = $this->serializer->serialize($avis, 'json');
       return new JsonResponse(data: $responseData, status: Response::HTTP_OK, json: true);
   }

/**
     * @OA\Put(
     *     path="/api/avis/{id}",
     *     summary="Update avis by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the avis"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="pseudo", type="string", example="Pseudo"),
     *             @OA\Property(property="commentaire", type="string", example="Commentaire"),
     *             @OA\Property(property="isVisible", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Avis updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Avis not found"
     *     )
     * )
     */


   #[Route('/{id}', name: 'edit', methods: 'PUT')]
   public function edit(int $id): JsonResponse
   {
       $avis = $this->repository->findOneBy(['id' => $id]);
       if (!$avis) {
           $this->manager->flush();
           return new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
       }

       return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
   }

    /**
     * @OA\Delete(
     *     path="/api/avis/{id}",
     *     summary="Delete avis by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the avis"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Avis deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Avis not found"
     *     )
     * )
     */


   #[Route('/{id}', name: 'delete', methods: 'DELETE')]
   public function delete(int $id): JsonResponse
   {
       $avis = $this->repository->findOneBy(['id' => $id]);
       if (!$avis) {
           return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
       }

       $this->manager->remove($avis);
       $this->manager->flush();

       return new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
   }
}