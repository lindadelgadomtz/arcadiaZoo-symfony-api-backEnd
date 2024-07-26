<?php

namespace App\Controller;

use App\Entity\RapportVeterinaire;
use App\Repository\RapportVeterinaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as OA;

#[Route('api/rapportVeterinaire', name: 'app_api_rapportVeterinaire_')]
class RapportVeterinaireController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private RapportVeterinaireRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * @OA\Post(
     *     path="/api/rapportVeterinaire",
     *     summary="Ajouter un rapport vétérinaire",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Les détails du rapport vétérinaire",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="date", type="string", format="date", example="2024-07-18"),
     *             @OA\Property(property="detail", type="string", example="Détails du rapport"),
     *             @OA\Property(property="animal", type="object", example={"id": 1}),
     *             @OA\Property(property="etat_animal", type="string", example="État de l'animal"),
     *             @OA\Property(property="nourriture", type="string", example="Type de nourriture"),
     *             @OA\Property(property="nourriture_grammage", type="integer", example=500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rapport vétérinaire enregistré avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="date", type="string", format="date", example="2024-07-18"),
     *             @OA\Property(property="detail", type="string", example="Détails du rapport"),
     *             @OA\Property(property="animal", type="object", example={"id": 1}),
     *             @OA\Property(property="etat_animal", type="string", example="État de l'animal"),
     *             @OA\Property(property="nourriture", type="string", example="Type de nourriture"),
     *             @OA\Property(property="nourriture_grammage", type="integer", example=500)
     *         )
     *     )
     * )
     */
    #[Route(methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        $rapportVeterinaire = $this->serializer->deserialize(
            $request->getContent(),
            RapportVeterinaire::class,
            'json'
        );

        $this->manager->persist($rapportVeterinaire);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($rapportVeterinaire, 'json');
        $location = $this->urlGenerator->generate(
            'app_api_rapportVeterinaire_show',
            ['id' => $rapportVeterinaire->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * @OA\Get(
     *     path="/api/rapportVeterinaire/{id}",
     *     summary="Obtenir un rapport vétérinaire par ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID du rapport vétérinaire"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du rapport vétérinaire",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="date", type="string", format="date", example="2024-07-18"),
     *             @OA\Property(property="detail", type="string", example="Détails du rapport"),
     *             @OA\Property(property="animal", type="object", example={"id": 1}),
     *             @OA\Property(property="etat_animal", type="string", example="État de l'animal"),
     *             @OA\Property(property="nourriture", type="string", example="Type de nourriture"),
     *             @OA\Property(property="nourriture_grammage", type="integer", example=500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rapport vétérinaire non trouvé"
     *     )
     * )
     */
    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): JsonResponse
    {
        $rapportVeterinaire = $this->repository->findOneBy(['id' => $id]);

        if (!$rapportVeterinaire) {
            return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
        }

        $responseData = $this->serializer->serialize($rapportVeterinaire, 'json');
        return new JsonResponse(data: $responseData, status: Response::HTTP_OK, json: true);
    }

    /**
     * @OA\Put(
     *     path="/api/rapportVeterinaire/{id}",
     *     summary="Mettre à jour un rapport vétérinaire par ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID du rapport vétérinaire"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Détails du rapport vétérinaire à mettre à jour",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="date", type="string", format="date", example="2024-07-18"),
     *             @OA\Property(property="detail", type="string", example="Détails du rapport"),
     *             @OA\Property(property="animal", type="object", example={"id": 1}),
     *             @OA\Property(property="etat_animal", type="string", example="État de l'animal"),
     *             @OA\Property(property="nourriture", type="string", example="Type de nourriture"),
     *             @OA\Property(property="nourriture_grammage", type="integer", example=500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Rapport vétérinaire mis à jour avec succès"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rapport vétérinaire non trouvé"
     *     )
     * )
     */
    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id, Request $request): JsonResponse
    {
        $rapportVeterinaire = $this->repository->findOneBy(['id' => $id]);
        if (!$rapportVeterinaire) {
            return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $rapportVeterinaire->setDate(new \DateTimeImmutable($data['date']));
        $rapportVeterinaire->setDetail($data['detail']);
        // Update animal association if needed
        // $rapportVeterinaire->setAnimal($animal);

        $this->manager->flush();
        return new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Delete(
     *     path="/api/rapportVeterinaire/{id}",
     *     summary="Supprimer un rapport vétérinaire par ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID du rapport vétérinaire"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Rapport vétérinaire supprimé avec succès"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rapport vétérinaire non trouvé"
     *     )
     * )
     */
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): JsonResponse
    {
        $rapportVeterinaire = $this->repository->findOneBy(['id' => $id]);
        if (!$rapportVeterinaire) {
            return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($rapportVeterinaire);
        $this->manager->flush();

        return new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
    }
}