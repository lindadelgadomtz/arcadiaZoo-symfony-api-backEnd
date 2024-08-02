<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\Gallery;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use DateTimeImmutable;
use OpenApi\Annotations as OA;

#[Route('api/service', name:'app_api_service_')]
class ServiceController extends AbstractController

{
    public function __construct(
        private EntityManagerInterface $manager, 
        private ServiceRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {
    }

    /**
     * @OA\Post(
     *     path="/api/service",
     *     summary="Ajouter un service",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Service à ajouter",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="nom", type="string", example="Nom du service"),
     *             @OA\Property(property="description", type="string", example="Description du service"),
     *             @OA\Property(property="createdAt", type="string", format="date-time", example="2024-07-18T14:30:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Service enregistré avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="nom", type="string", example="Nom du service"),
     *             @OA\Property(property="description", type="string", example="Description du service"),
     *             @OA\Property(property="createdAt", type="string", format="date-time", example="2024-07-18T14:30:00Z")
     *         )
     *     )
     * )
     */

     #[Route(methods: ['POST'])]
public function new(Request $request): JsonResponse
{
    // Decode the JSON request content
    $data = json_decode($request->getContent(), true);
    
    // Extract the gallery ID from the request
    $galleryId = $data['gallery'] ?? null;
    
    // Fetch the Gallery entity from the repository
    $gallery = $this->manager->getRepository(Gallery::class)->find($galleryId);

    // Check if the Gallery exists
    if (!$gallery && $galleryId !== null) {
        return new JsonResponse(['error' => 'Gallery not found'], Response::HTTP_NOT_FOUND);
    }

    // Deserialize the request content into a Service entity
    $service = $this->serializer->deserialize(
        $request->getContent(),
        Service::class,
        'json'
    );
    
    // Set the Gallery entity on the Service if it exists
    if ($gallery) {
        $service->setGallery($gallery);
    }
    $service->setCreatedAt(new DateTimeImmutable());

    // Tell Doctrine you want to (eventually) save the service (no queries yet)
    $this->manager->persist($service);
    // Actually execute the queries (i.e. the INSERT query)
    $this->manager->flush();

    // Include the gallery ID in the response
    $responseData = $this->serializer->serialize($service, 'json');
    $serviceWithGalleryId = json_decode($responseData, true); // Decode to array
    if ($service->getGallery()) {
        $serviceWithGalleryId['gallery'] = $service->getGallery()->getId(); // Add the gallery ID
    }
    $responseData = json_encode($serviceWithGalleryId); // Encode back to JSON

    $location = $this->urlGenerator->generate(
        'app_api_service_show',
        ['id' => $service->getId()],
        UrlGeneratorInterface::ABSOLUTE_URL
    );
    
    return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
}

     
        /**
     * @OA\Get(
     *     path="/api/service/{id}",
     *     summary="Get service by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the service"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="nom", type="string", example="Nom du service"),
     *             @OA\Property(property="description", type="string", example="Description du service"),
     *             @OA\Property(property="createdAt", type="string", format="date-time", example="2024-07-18T14:30:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found"
     *     )
     * )
     */


    #[Route('/{id}', name: 'show', methods: ['GET'])] 
    public function show(int $id): JsonResponse
    {
        $service = $this->repository->findOneBy(['id' => $id]);

        if (!$service) {
            return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
        }

        $responseData = $this->serializer->serialize($service, 'json');
        return new JsonResponse(data: $responseData, status: Response::HTTP_OK, json: true);
    }

    /**
     * @OA\Put(
     *     path="/api/service/{id}",
     *     summary="Update service by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the service"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="nom", type="string", example="Nom du service"),
     *             @OA\Property(property="description", type="string", example="Description du service"),
     *             @OA\Property(property="createdAt", type="string", format="date-time", example="2024-07-18T14:30:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Service updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found"
     *     )
     * )
     */


     #[Route('/{id}', name: 'edit', methods: ['PUT'])]
     public function edit(Request $request, int $id): JsonResponse
     {
         $service = $this->repository->findOneBy(['id' => $id]);
         if (!$service) {
             return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
         }
 
         $updatedService = $this->serializer->deserialize(
             $request->getContent(),
             Service::class,
             'json'
         );
         
         // Update service properties
         $service->setNom($updatedService->getNom() ?? $service->getNom());
         $service->setDescription($updatedService->getDescription() ?? $service->getDescription());
         $service->setCreatedAt($updatedService->getCreatedAt() ?? $service->getCreatedAt());
 
         $this->manager->flush();
 
         return new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
     }

        /**
     * @OA\Delete(
     *     path="/api/service/{id}",
     *     summary="Delete service by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the service"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Service deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found"
     *     )
     * )
     */


    #[Route('/{id}', name: 'delete', methods: ['DELETE'])] 
    public function delete(int $id): JsonResponse
    {
        $service = $this->repository->findOneBy(['id' => $id]);
        if (!$service) {
            return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($service);
        $this->manager->flush();

        return new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
    }
}
