<?php
declare(strict_types=1);

/**
 *
 * @author Mr Alexandre J-S William ELISÉ <code@apiadept.com>
 * @copyright (c) 2009 - present. Mr Alexandre J-S William ELISÉ. All rights reserved.
 * @license GNU Affero General Public License version 3 (AGPLv3)
 * @link https://apiadept.com
 */

defined('GRILLE_LIGNE') || define('GRILLE_LIGNE', 6);
defined('GRILLE_COLONNE') || define('GRILLE_COLONNE', 7);

defined('PION_ROUGE') || define('PION_ROUGE', "\033[31;1m@\033[0m");
defined('PION_JAUNE') || define('PION_JAUNE', "\033[33;1m@\033[0m");

// Le nombre de pions à aligner pour gagner la partie
defined('PION_ALIGNES') || define('PION_ALIGNES', 4);

// Lab
defined('NB_COUP_MINIMAL_JOUEUR_2_GAGNE') || define('NB_COUP_MINIMAL_JOUEUR_2_GAGNE', 9);
defined('TOLERANCE_ERREUR_EN_POURCENTAGE') || define('TOLERANCE_ERREUR_EN_POURCENTAGE', (float)($_SERVER['TOLERANCE_ERREUR'] ?? 0.01));

/**
 *
 */

/**
 *
 */
enum _Case: int
{
    case ROUGE = 1;
    case JAUNE = 2;
    case VIDE = 0;
}

/**
 *
 */

/**
 *
 */
enum Niv: string
{
    case FACILE = 'F';
    case NORMAL = 'M';
    case DIFFICILE = 'D';
}

/**
 *
 */

/**
 *
 */
class Joueur
{
    public string $nom;
    public array $nb_joue;
    public array $win;
    public Niv $niveau;
}


/**
 * @return array
 */
function commencement(): array
{
    $grille = [];
    for ($h = 5; $h >= 0; --$h) {
        $grille[$h] = [];
        for ($c = 0; $c <= 6; ++$c) {
            $grille[$h][$c] = _Case::VIDE->value;
        }
    }
    return $grille;
}

/**
 * @return Joueur
 */
function nouveauJoueur(?string $votreNomJoueur = null, ?string $votreNiveauJoueur = null): Joueur
{
    $niveau = '';
    $joueur = new Joueur();


    printf("\033[37;40mCreation d'un joueur\033[0m\n");

    printf('Nom du joueur:');
    if ($votreNomJoueur == null) {
        $nomJoueur = trim(fgets(STDIN, 30));
    } else {
        $nomJoueur = $votreNomJoueur;
    }

    // F = 70 M = 77 D = 68
    while ($niveau != 'F' && $niveau != 'M' && $niveau != 'D') {
        printf('Niveau du joueur: (F,M,D)');
        if ($votreNiveauJoueur == null) {
            fscanf(STDIN, "%s\n", $niveau);
        } else {
            $niveau = $votreNiveauJoueur;
        }
    }


    switch ($niveau) {
        case 'F':
            $joueur->niveau = Niv::FACILE;
            break;
        case 'M':
            $joueur->niveau = Niv::NORMAL;
            break;
        case 'D':
            $joueur->niveau = Niv::DIFFICILE;
            break;
        default:

    }
    $joueur->nom = $nomJoueur;


    $joueur->nb_joue[Niv::FACILE->value] = 0;
    $joueur->win[Niv::FACILE->value] = 0;

    $joueur->nb_joue[Niv::NORMAL->value] = 0;
    $joueur->win[Niv::NORMAL->value] = 0;

    $joueur->nb_joue[Niv::DIFFICILE->value] = 0;
    $joueur->win[Niv::DIFFICILE->value] = 0;

    return $joueur;
}

/**
 * @param array $tab
 * @param int $colonne
 * @param int $ligne
 * @return bool
 */
function estPartieTerminee(array $tab, int $nombreCoup): bool
{
    $couleur = ($nombreCoup % 2 == 0) ? _Case::ROUGE->value : _Case::JAUNE->value;

    for ($h = 5; $h >= 0; --$h) {
        for ($c = 0; $c <= 6; ++$c) {

            $hz = verifierAlignement($h, $c, 0, 1, PION_ALIGNES, $couleur, $tab);
            $vt = verifierAlignement($h, $c, 1, 0, PION_ALIGNES, $couleur, $tab);
            $d1 = verifierAlignement($h, $c, 1, 1, PION_ALIGNES, $couleur, $tab);
            $d2 = verifierAlignement($h, $c, 1, -1, PION_ALIGNES, $couleur, $tab);

            if ($hz || $vt || $d1 || $d2) {
                return true;
            }
        }
    }

    return false;
}

/**
 * @param int $r0
 * @param int $c0
 * @param int $dr
 * @param int $dc
 * @param int $len
 * @param int $num
 *
 * @return bool
 */
function verifierAlignement(int $r0, int $c0, int $dr, int $dc, int $len, int $num, array $tab): bool
{
    for ($k = 0; $k < $len; ++$k) {
        $r = $r0 + ($k * $dr);
        $c = $c0 + ($k * $dc);
        if (($r < 0) || ($c < 0) || ($r >= GRILLE_LIGNE) || ($c >= GRILLE_COLONNE) || ($num !== $tab[$r][$c])) {
            return false;
        }
    }

    return ($len === PION_ALIGNES);
}

/**
 * @param array $tab
 * @return void
 */
function dessinePlateau(array $tab): void
{
    printf("\n");
    $i = GRILLE_LIGNE;
    while ($i-- > 0) {
        for ($j = 0; $j < GRILLE_COLONNE; $j++) {
            printf('¦  ');
            if ($tab[$i][$j] == _Case::ROUGE->value) {
                printf(PION_ROUGE);
            } elseif ($tab[$i][$j] == _Case::JAUNE->value) {
                printf(PION_JAUNE);
            } else {
                printf(' ');
            }
            printf('  ');
        }
        printf("¦\n");
        for ($j = 0; $j < GRILLE_COLONNE; $j++) {
            printf('+-----');
        }
        printf("+\n");
    }
    for ($j = 1; $j <= GRILLE_COLONNE; $j++) {
        printf('   %d  ', $j);
    }
    printf("\n");
}

/**
 * @param array $tab
 * @param int $num_col
 * @param int $pion
 * @return int
 */
function jouerCoup(array &$tab, int $num_col, int $pion): int
{
    $l = 0;
    $trouve = 0;
//  $num_col--;
    while (!$trouve && ($l < GRILLE_LIGNE)) {
        $trouve = ($tab[$l][$num_col] == _Case::VIDE->value);
        if ($trouve) {
            $tab[$l][$num_col] = $pion;
        } else {
            $l++;
        }
    }
    if ($trouve == 0) {
        return -1;
    }

    return $l;
}

/**
 * @param Joueur $joueur
 * @return void
 */
function afficheStatistiquesJoueur(Joueur $joueur): void
{
    $niveauString = 'FACILE';
    switch ($joueur->niveau->value) {
        case Niv::FACILE->value:
            $niveauString = 'FACILE';
            break;
        case Niv::NORMAL->value:
            $niveauString = 'NORMAL';
            break;
        case Niv::DIFFICILE->value:
            $niveauString = 'DIFFICILE';
            break;
    }

    printf("\033[37;40m Statistique de %s - Niveau %s : \033[0m \n", $joueur->nom, $niveauString);
    printf("Partie faciles Jouées    : %02d | Gagnées : %02d \n", $joueur->nb_joue[Niv::FACILE->value], $joueur->win[Niv::FACILE->value]);
    printf("Parie normales Jouées    : %02d | Gagnées : %02d \n", $joueur->nb_joue[Niv::NORMAL->value], $joueur->win[Niv::NORMAL->value]);
    printf("Partie difficiles Jouées : %02d | Gagnées : %02d\n", $joueur->nb_joue[Niv::DIFFICILE->value], $joueur->win[Niv::DIFFICILE->value]);
}

function main(): void
{
    $quitter = 'Q';

    while ($quitter != 'C') {

        printf("Entrez les informations du joueur 1 (ROUGE) : \n");
        $joueur1 = nouveauJoueur();
        printf("\n\n");
        printf("Entrez les informations du joueur 2 (JAUNE) : \n");
        $joueur2 = nouveauJoueur();


        $rejouer = 'O';

        while ($rejouer == 'O') {
            afficheStatistiquesJoueur($joueur1);
            afficheStatistiquesJoueur($joueur2);
            $rejouer = false;
            // Initialiser la nouvelle grille
            $grille = commencement();


            //Afficher le grille
            dessinePlateau($grille);

            $partieTerminee = false;
            $nbCoup = 0;


            while (!$partieTerminee && ($nbCoup < GRILLE_COLONNE * GRILLE_LIGNE)) {
                // Choix  - $colonne joueur
                $colonneJoue = -1;
                // mode_raw(1);
                $hauteurPion = -1;
                while ($hauteurPion == -1) {

                    while ($colonneJoue < 1 || $colonneJoue > 7) {
                        if (($nbCoup % 2) == 0) {
                            printf("\033[1;41;30m A %s en ROUGE de jouer\033[0m \n", $joueur1->nom);
                        } else {
                            printf("\033[1;43;30m A %s JAUNE de jouer \033[0m \n", $joueur2->nom);
                        }

                        printf("Tapez une touche entre 1 et 7 :\n");
                        fscanf(STDIN, '%d', $colonneJoue);
                    }

                    // mode_raw(0);
                    $colonneJoue--;

                    // Joue le coup
                    $hauteurPion = jouerCoup($grille, $colonneJoue, (($nbCoup % 2) == 0) ? _Case::ROUGE->value : _Case::JAUNE->value);
                    if ($hauteurPion == -1) {
                        printf("Coup impossible!!!\n");
                    } else {
                        $nbCoup++;
                    }
                }

                // Affiche la grille
                dessinePlateau($grille);


                // La partie est-elle terminée ?
                if (estPartieTerminee($grille, $nbCoup)) {
                    $partieTerminee = true;
                }

            }

            if (!$partieTerminee) {
                printf('EGALITE');
            } else {
                if ($nbCoup % 2 == 0) {
                    printf("Les ROUGES ont gagnés\n");
                    $joueur1->win[$joueur2->niveau->value]++;
                } else {
                    printf("Les JAUNES ont gagnés\n");
                    $joueur2->win[$joueur1->niveau->value]++;
                }
                $joueur1->nb_joue[$joueur2->niveau->value]++;
                $joueur2->nb_joue[$joueur1->niveau->value]++;
            }
// Demander si les joueurs veulent rejouer $i
// 79 N
// 78 O
            printf("Rejouer (O/N)\n");
            while ($rejouer != 'O' && $rejouer != 'N') {
                fscanf(STDIN, '%s', $rejouer);
            }


        }

        printf("Quitter ou Continuer (Q/C)\n");
        $quitter = '';
        while ($quitter != 'Q' && $quitter != 'C') {
            fscanf(STDIN, '%s', $quitter);
        }

    }

    exit(0);
}


function aleatoire(): void
{
    $quitter = 'Q';
    $rejouer = 'O';

    while ($quitter != 'C') {
        $dateDebutPartie = (new DateTimeImmutable('now', new DateTimeZone('America/Martinique')))->format('Y-m-d\TH:iP');

        printf("Entrez les informations du joueur 1 (ROUGE) : \n");
        $joueur1 = nouveauJoueur('robot-1', 'D');
        printf("\n\n");
        printf("Entrez les informations du joueur 2 (JAUNE) : \n");
        $joueur2 = nouveauJoueur('robot-2', 'D');


        while ($rejouer == 'O') {
            afficheStatistiquesJoueur($joueur1);
            afficheStatistiquesJoueur($joueur2);
            $rejouer = false;
            // Initialiser la nouvelle grille
            $grille = commencement();


            //Afficher le grille
            dessinePlateau($grille);

            $partieTerminee = false;
            $nbCoup = 0;


            while (!$partieTerminee && ($nbCoup < GRILLE_COLONNE * GRILLE_LIGNE)) {
                // Choix  - $colonne joueur
                $colonneJoue = -1;
                // mode_raw(1);
                $hauteurPion = -1;
                while ($hauteurPion == -1) {

                    while ($colonneJoue < 1 || $colonneJoue > 7) {
                        if (($nbCoup % 2) == 0) {
                            printf("\033[1;41;30m A %s en ROUGE de jouer\033[0m \n", $joueur1->nom);
                        } else {
                            printf("\033[1;43;30m A %s JAUNE de jouer \033[0m \n", $joueur2->nom);
                        }

                        printf("Tapez une touche entre 1 et 7 :\n");
                        $colonneJoue = random_int(1, 7);
                    }

                    // mode_raw(0);
                    $colonneJoue--;

                    // Joue le coup
                    $pionActuel = (($nbCoup % 2) == 0) ? _Case::ROUGE->value : _Case::JAUNE->value;
                    $hauteurPion = jouerCoup($grille, $colonneJoue, $pionActuel);
                    if ($hauteurPion == -1) {
                        printf("Coup impossible!!!\n");
                    } else {
                        file_put_contents(sprintf('puissance-4-joueur-%d-actuel-nbcoup-%d-colonne-%d-date-%s.txt', $pionActuel, $nbCoup, $colonneJoue, $dateDebutPartie), serialize($grille));
                        $nbCoup++;
                    }
                }

                // Affiche la grille
                dessinePlateau($grille);


                // La partie est-elle terminée ?
                if (estPartieTerminee($grille, $nbCoup)) {
                    $partieTerminee = true;
                }

            }

            if (!$partieTerminee) {
                printf('EGALITE');
            } else {
                if ($nbCoup % 2 == 0) {
                    printf("Les ROUGES ont gagnés\n");
                    $joueur1->win[$joueur2->niveau->value]++;
                } else {
                    printf("Les JAUNES ont gagnés\n");
                    $joueur2->win[$joueur1->niveau->value]++;
                    file_put_contents(sprintf('puissance-4-joueur-2-gagne-nbcoup-%d-date-%s.txt', $nbCoup, $dateDebutPartie), serialize($grille));
                }
                $joueur1->nb_joue[$joueur2->niveau->value]++;
                $joueur2->nb_joue[$joueur1->niveau->value]++;
            }
// Demander si les joueurs veulent rejouer $i
// 79 N
// 78 O
            printf("Rejouer (O/N)\n");
            $victoiresJoueur2 = glob("puissance-4-joueur-2-gagne-*.txt");

            // Rejouer tant que joueur 2 ne gagne pas avec un nombre de coup minimal ou coup gagnant
            $rejouer = count(array_values(array_filter(
                $victoiresJoueur2,
                function ($v) {
                    // Si valeur tolerance erreur est invalide, ne pas prendre en compte l'element en cours
                    if ((TOLERANCE_ERREUR_EN_POURCENTAGE < 0) || (TOLERANCE_ERREUR_EN_POURCENTAGE > 0.999)) {
                        return false;
                    }

                    $sortie = preg_match('#puissance\-4\-joueur\-2\-gagne\-nbcoup\-(\d{1,2})#', $v, $out) > 0;
                    if (!$sortie) {
                        return false;
                    }

                    // Le joueur 2 gagne dans le meilleur des cas en 9 coups minimum (coup 0 c'est le joueur 1)
                    // 0 à 8 (4 pions rouges + 4 pions jaunes) les rouges font une erreur au coup numéro 7
                    // cela donne une opportunité au pions jaunes de gagner en un minimum de 9 coups

                    return (
                        ($out[1] % 2 === 1)
                        && (
                            (NB_COUP_MINIMAL_JOUEUR_2_GAGNE <= $out[1])
                            && ($out[1] < (NB_COUP_MINIMAL_JOUEUR_2_GAGNE * (1 + TOLERANCE_ERREUR_EN_POURCENTAGE)))
                        )
                    );
                }
            ))) ? 'N' : 'O';
        }

        printf("Quitter ou Continuer (Q/C)\n");

        // Tant que le joueur 2 ne gagne pas recommencer sinon quitter
        $quitter = ($rejouer == 'N') ? 'C' : 'Q';
    }

    exit(0);
}

function web()
{
    echo 'Pas encore créé' . PHP_EOL;
    exit(0);
}

if (!($_SERVER['MODE_DE_JEU'] ?? false)) {
    main();
    return;
}

switch ($_SERVER['MODE_DE_JEU']) {
    case 'aleatoire':
        aleatoire();
        break;
    case 'web':
        web();
        break;
    case 'importer':
        echo 'Préciser la grille a importer grâce à la variable d\'environement GRILLE_JEU' . PHP_EOL;
        $fichier = (!empty($_SERVER['GRILLE_JEU']) && is_file($_SERVER['GRILLE_JEU']) && (filesize($_SERVER['GRILLE_JEU']) > 0)) ? $_SERVER['GRILLE_JEU'] : '';
        if (empty($fichier)) {
            echo 'Impossible importer partie. Fichier specifié a un problème ou n\'existe pas' . PHP_EOL;
            exit(1);
        }
        // Afficher grille importée ou grille vide du commencement de la partie
        dessinePlateau(unserialize(file_get_contents($fichier)) ?? commencement());
        break;
    default:
        main();
}
