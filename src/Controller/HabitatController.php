<?php

namespace App\Controller;

use App\Entity\Habitat;
use App\Repository\HabitatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as OA;

#[Route('api/habitat', name: 'app_api_habitat_')]
class HabitatController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private HabitatRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @OA\Post(
     *     path="/api/habitat",
     *     summary="Create a new habitat",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="nom", type="string", example="Forest"),
     *             @OA\Property(property="description", type="string", example="A large forest"),
     *             @OA\Property(property="commentaire_habitat", type="string", example="This is a comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Habitat created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="nom", type="string", example="Forest"),
     *             @OA\Property(property="description", type="string", example="A large forest"),
     *             @OA\Property(property="commentaire_habitat", type="string", example="This is a comment")
     *         )
     *     )
     * )
     */
    #[Route(methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        $habitat = $this->serializer->deserialize(
            $request->getContent(),
            Habitat::class,
            'json'
        );

        // Tell Doctrine you want to (eventually) save the habitat (no queries yet)
        $this->manager->persist($habitat);
        // Actually executes the queries (i.e. the INSERT query)
        $this->manager->flush();

        $responseData = $this->serializer->serialize($habitat, 'json');
        $location = $this->urlGenerator->generate(
            'app_api_habitat_show',
            ['id' => $habitat->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * @OA\Get(
     *     path="/api/habitat/{id}",
     *     summary="Get habitat by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the habitat"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Habitat details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="nom", type="string", example="Forest"),
     *             @OA\Property(property="description", type="string", example="A large forest"),
     *             @OA\Property(property="commentaire_habitat", type="string", example="This is a comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Habitat not found"
     *     )
     * )
     */
    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): JsonResponse
    {
        $habitat = $this->repository->findOneBy(['id' => $id]);

        if (!$habitat) {
            return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
        }

        $responseData = $this->serializer->serialize($habitat, 'json');
        return new JsonResponse(data: $responseData, status: Response::HTTP_OK, json: true);
    }

    /**
     * @OA\Put(
     *     path="/api/habitat/{id}",
     *     summary="Update habitat by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the habitat"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="nom", type="string", example="Forest"),
     *             @OA\Property(property="description", type="string", example="A large forest"),
     *             @OA\Property(property="commentaire_habitat", type="string", example="This is a comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Habitat updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Habitat not found"
     *     )
     * )
     */
    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id, Request $request): JsonResponse
    {
        $habitat = $this->repository->findOneBy(['id' => $id]);
        if (!$habitat) {
            return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $habitat->setNom($data['nom'] ?? $habitat->getNom());
        $habitat->setDescription($data['description'] ?? $habitat->getDescription());
        $habitat->setCommentaireHabitat($data['commentaire_habitat'] ?? $habitat->getCommentaireHabitat());

        $this->manager->flush();
        return new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Delete(
     *     path="/api/habitat/{id}",
     *     summary="Delete habitat by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the habitat"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Habitat deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Habitat not found"
     *     )
     * )
     */
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): JsonResponse
    {
        $habitat = $this->repository->findOneBy(['id' => $id]);
        if (!$habitat) {
            return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($habitat);
        $this->manager->flush();

        return new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
    }
}
