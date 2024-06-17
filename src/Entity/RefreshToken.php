<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken as BaseRefreshToken;

#[ApiResource]
#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
class RefreshToken extends BaseRefreshToken
{
}
