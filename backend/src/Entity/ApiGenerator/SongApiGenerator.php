<?php

declare(strict_types=1);

namespace App\Entity\ApiGenerator;

use App\Container\EntityManagerAwareTrait;
use App\Entity\Api\ResolvableUrl;
use App\Entity\Api\Song;
use App\Entity\Interfaces\SongInterface;
use App\Entity\Repository\CustomFieldRepository;
use App\Entity\Repository\StationRepository;
use App\Entity\Station;
use App\Entity\StationMedia;
use App\Http\Router;
use App\Media\RemoteAlbumArt;
use GuzzleHttp\Psr7\UriResolver;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\UriInterface;

final class SongApiGenerator
{
    use EntityManagerAwareTrait;

    public function __construct(
        private readonly Router $router,
        private readonly StationRepository $stationRepo,
        private readonly CustomFieldRepository $customFieldRepo,
        private readonly RemoteAlbumArt $remoteAlbumArt
    ) {
    }

    public function __invoke(
        SongInterface $song,
        ?Station $station = null,
        ?UriInterface $baseUri = null,
        bool $allowRemoteArt = false,
        bool $isNowPlaying = false,
    ): Song {
        $response = new Song();
        $response->id = $song->getSongId();
        $response->text = $song->getText() ?? '';
        $response->artist = $song->getArtist() ?? '';
        $response->title = $song->getTitle() ?? '';
        $response->album = $song->getAlbum() ?? '';

        if ($song instanceof StationMedia) {
            $response->genre = $song->getGenre() ?? '';
            $response->isrc = $song->getIsrc() ?? '';
            $response->lyrics = $song->getLyrics() ?? '';

            $response->custom_fields = $this->getCustomFields($song->getId());
        } else {
            $response->custom_fields = $this->getCustomFields();
        }

        $response->art = new ResolvableUrl(
            UriResolver::resolve(
                $baseUri ?? $this->router->getBaseUrl(),
                $this->getAlbumArtUrl($song, $station, $allowRemoteArt, $isNowPlaying)
            )
        );

        return $response;
    }

    private function getAlbumArtUrl(
        SongInterface $song,
        ?Station $station = null,
        bool $allowRemoteArt = false,
        bool $isNowPlaying = false,
    ): UriInterface {
        if (null !== $station && $song instanceof StationMedia) {
            $routeParams = [
                'station_id' => $station->getShortName(),
                'media_id' => $song->getUniqueId(),
            ];

            $mediaUpdatedTimestamp = $song->getArtUpdatedAt();
            if (0 !== $mediaUpdatedTimestamp) {
                $routeParams['timestamp'] = $mediaUpdatedTimestamp;
            }

            return $this->router->namedAsUri(
                routeName: 'api:stations:media:art',
                routeParams: $routeParams
            );
        }

        if ($allowRemoteArt && $this->remoteAlbumArt->enableForApis()) {
            $url = $this->remoteAlbumArt->getUrlForSong($song);
            if (null !== $url) {
                return Utils::uriFor($url);
            }
        }

        if ($isNowPlaying && null !== $station) {
            $currentStreamer = $station->getCurrentStreamer();
            if (null !== $currentStreamer && 0 !== $currentStreamer->getArtUpdatedAt()) {
                return $this->router->namedAsUri(
                    routeName: 'api:stations:streamer:art',
                    routeParams: [
                        'station_id' => $station->getShortName(),
                        'id' => $currentStreamer->getIdRequired(),
                        'timestamp' => $currentStreamer->getArtUpdatedAt(),
                    ],
                );
            }
        }

        return $this->stationRepo->getDefaultAlbumArtUrl($station);
    }

    /**
     * Return all custom fields, either with a null value or with the custom value assigned to the given Media ID.
     *
     * @param int|null $mediaId
     *
     * @return mixed[]
     */
    private function getCustomFields(?int $mediaId = null): array
    {
        $fields = $this->customFieldRepo->getFieldIds();

        $mediaFields = [];
        if ($mediaId !== null) {
            $mediaFieldsRaw = $this->em->createQuery(
                <<<'DQL'
                    SELECT smcf.field_id, smcf.value
                    FROM App\Entity\StationMediaCustomField smcf
                    WHERE smcf.media_id = :media_id
                DQL
            )->setParameter('media_id', $mediaId)
                ->getArrayResult();

            foreach ($mediaFieldsRaw as $row) {
                $mediaFields[$row['field_id']] = $row['value'];
            }
        }

        $customFields = [];
        foreach ($fields as $fieldId => $fieldKey) {
            $customFields[$fieldKey] = $mediaFields[$fieldId] ?? null;
        }
        return $customFields;
    }
}
