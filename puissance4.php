<?php

declare(strict_types=1);

/**
 *
 * @author        Mr Alexandre J-S William ELISÉ <code@apiadept.com>
 * @copyright (c) 2009 - present. Mr Alexandre J-S William ELISÉ. All rights reserved.
 * @license       GNU Affero General Public License version 3 (AGPLv3)
 * @link          https://apiadept.com
 */

defined('GRILLE_LIGNE') || define('GRILLE_LIGNE', 6);
defined('GRILLE_COLONNE') || define('GRILLE_COLONNE', 7);

defined('PION_ROUGE') || define('PION_ROUGE', "\033[31;1m@\033[0m");
defined('PION_JAUNE') || define('PION_JAUNE', "\033[33;1m@\033[0m");

// Fr: Le nombre de pions à aligner pour gagner la partie
// En: How many piece to align to win the game
defined('PION_ALIGNES') || define('PION_ALIGNES', 4);

// Fr: Lab: Essayer avec un nombre minimal de coups à jouer pour le joueur 2
// En: Lab: Try with a minimal plies to play for the player 2
defined('NB_COUP_MINIMAL_JOUEUR_2_GAGNE') || define('NB_COUP_MINIMAL_JOUEUR_2_GAGNE', 9);

// Fr: Tolérance entre 0 et 0.999 (0 veut dire que le joueur 2 a gagné TOUTES les parties jouées)
// En; Tolerance between 0 and 0.999 (0 means that the player 2 won ALL games it played)
defined('TOLERANCE') || define('TOLERANCE', max(0.0, min(1.0, (float)($_SERVER['TOLERANCE'] ?? 0.01))));

// Fr: Maximum de parties jouées pour "entrainer" l'algorithme simplifié d'apprentissage automatique supervisé (choisir de préférence un multiple de 2)
// En: Maximum games to play to "train" the simplified algorithm of supervised learning (preferably choose a number that is a power of 2)
defined('MAX_ECHANTILLONS') || define(
    'MAX_ECHANTILLONS',
    max(2, min(2048, (int)($_SERVER['MAX_ECHANTILLONS'] ?? 2048))),
);

// Fr: Pour que ce soit compatible avec les anciennes versions de PHP au lieu d'utiliser les enums (pas strictement equivalent mais bon)
// En: This is done to be compatible with older versions of PHP instead of using enums (not strictly equivalent but you get the idea)
defined('CASE_ROUGE') || define('CASE_ROUGE', 1);
defined('CASE_JAUNE') || define('CASE_JAUNE', 2);
defined('CASE_VIDE') || define('CASE_VIDE', 0);

// Fr: Même raison que pour les niveaux de difficulté du jeu
// En: Same reason than above but for difficulty level of the game
defined('NIV_FACILE') || define('NIV_FACILE', 'F');
defined('NIV_NORMAL') || define('NIV_NORMAL', 'M');
defined('NIV_DIFFICILE') || define('NIV_DIFFICILE', 'D');

/**
 * Fr: DTO representant un joueur.euse. (struct dans le code originel en C)
 * En: DTO representing a gamer (struct in the original source code in C)
 */
class Joueur
{
    public $nom = 'Alex';
    public $nb_joue = [];
    public $win = [];
    public $niveau = 'F';
}


/**
 * Fr: Initialiser la grille de jeu
 * En: Initialise game board
 * @return array
 */
function commencement(): array
{
    $grille = [];
    for ($h = 5; $h >= 0; --$h) {
        $grille[$h] = [];
        for ($c = 0; $c <= 6; ++$c) {
            $grille[$h][$c] = CASE_VIDE;
        }
    }

    return $grille;
}

/**
 * Fr: Remplir vos infos minimales de joueur.euse (nom, niveau de difficulté)
 * En: Fill out your minimal gamer info (name, difficulty level)
 *
 * @param   string|null  $votreNomJoueur
 * @param   string|null  $votreNiveauJoueur
 *
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
            $joueur->niveau = NIV_FACILE;
            break;
        case 'M':
            $joueur->niveau = NIV_NORMAL;
            break;
        case 'D':
            $joueur->niveau = NIV_DIFFICILE;
            break;
        default:
    }
    $joueur->nom = $nomJoueur;


    $joueur->nb_joue[NIV_FACILE] = 0;
    $joueur->win[NIV_FACILE]     = 0;

    $joueur->nb_joue[NIV_NORMAL] = 0;
    $joueur->win[NIV_NORMAL]     = 0;

    $joueur->nb_joue[NIV_DIFFICILE] = 0;
    $joueur->win[NIV_DIFFICILE]     = 0;

    return $joueur;
}

/**
 * Fr: Detecter la fin du jeu
 * En: Detect end of the game
 *
 * @param   array  $tab
 * @param   int    $nombreCoup
 *
 * @return bool
 */
function estPartieTerminee(array $tab, int $nombreCoup): bool
{
    $couleur = ($nombreCoup % 2 == 0) ? CASE_ROUGE : CASE_JAUNE;

    for ($h = 5; $h >= 0; --$h) {
        for ($c = 0; $c <= 6; ++$c) {
            $hz = verifierAlignement($h, $c, 0, 1, PION_ALIGNES, $couleur, $tab);
            $vt = verifierAlignement($h, $c, 1, 0, PION_ALIGNES, $couleur, $tab);
            $d1 = verifierAlignement($h, $c, 1, 1, PION_ALIGNES, $couleur, $tab);
            $d2 = verifierAlignement($h, $c, 1, -1, PION_ALIGNES, $couleur, $tab);

            if (((($hz xor $vt) xor $d1) xor $d2)) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Fr: Algorithme detection alignement basé sur les règles du jeu Puissance 4
 * En: Alignment detection algorithm based on Connect 4 game rules
 *
 * @param   int    $r0
 * @param   int    $c0
 * @param   int    $dr
 * @param   int    $dc
 * @param   int    $len
 * @param   int    $num
 * @param   array  $tab
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

    // Fr: k doit être exactement égal à PION_ALIGNES (nombre de pions alignes de meme couleur)
    // En: K must be exactly equal to PION_ALIGNES ( number of aligned pieces same color)
    return ($k === PION_ALIGNES);
}

/**
 * Fr: Dessine la grille de jeu
 * En:  Renders the game board
 *
 * @param   array  $tab
 *
 * @return void
 */
function dessinePlateau(array $tab): void
{
    printf("\n");
    $i = GRILLE_LIGNE;
    while ($i-- > 0) {
        for ($j = 0; $j < GRILLE_COLONNE; $j++) {
            printf('¦  ');
            if ($tab[$i][$j] == CASE_ROUGE) {
                printf(PION_ROUGE);
            } elseif ($tab[$i][$j] == CASE_JAUNE) {
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
 * Fr: Jouer un coup quand c'est à votre tour
 * En: Play a ply when it's your turn
 *
 * @param   array  $tab
 * @param   int    $num_col
 * @param   int    $pion
 *
 * @return int
 */
function jouerCoup(array &$tab, int $num_col, int $pion): int
{
    $l      = 0;
    $trouve = 0;
//  $num_col--;
    while (!$trouve && ($l < GRILLE_LIGNE)) {
        $trouve = ($tab[$l][$num_col] == CASE_VIDE);
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
 * Fr: Affiche statistiques joueur.euse
 * En: Display gamer stats
 *
 * @param   Joueur  $joueur
 *
 * @return void
 */
function afficheStatistiquesJoueur(Joueur $joueur): void
{
    switch ($joueur->niveau) {
        case NIV_FACILE:
            $niveauString = 'FACILE';
            break;
        case NIV_NORMAL:
            $niveauString = 'NORMAL';
            break;
        case NIV_DIFFICILE:
            $niveauString = 'DIFFICILE';
            break;
        default:
            $niveauString = 'FACILE';
    }

    printf("\033[37;40m Statistique de %s - Niveau %s : \033[0m \n", $joueur->nom, $niveauString);
    printf(
        "Partie faciles Jouées    : %02d | Gagnées : %02d \n",
        $joueur->nb_joue[NIV_FACILE],
        $joueur->win[NIV_FACILE],
    );
    printf(
        "Partie normales Jouées    : %02d | Gagnées : %02d \n",
        $joueur->nb_joue[NIV_NORMAL],
        $joueur->win[NIV_NORMAL],
    );
    printf(
        "Partie difficiles Jouées : %02d | Gagnées : %02d\n",
        $joueur->nb_joue[NIV_DIFFICILE],
        $joueur->win[NIV_DIFFICILE],
    );
}

/**
 * Fr: Programme principal
 * En: Main program
 *
 * @return void
 * @throws Exception
 */
function main(): void
{
    $rejouer = 'O';

    do {
        // En: Enter Player 1 (RED) information
        printf("Entrez les informations du joueur 1 (ROUGE) : \n");
        $joueur1 = nouveauJoueur();
        printf("\n\n");

        // En: Enter Player 2 (YELLOW) information
        printf("Entrez les informations du joueur 2 (JAUNE) : \n");
        $joueur2 = nouveauJoueur();

        while ($rejouer == 'O') {
            // Fr: Horodatage debut partie
            // En: Start game datetime
            $dateDebutPartie = gmdate(DateTimeInterface::RFC3339);

            // En: Player 1 game stats
            afficheStatistiquesJoueur($joueur1);

            // En: Player 2 game stats
            afficheStatistiquesJoueur($joueur2);
            $rejouer = false;
            // Fr: Initialiser la nouvelle grille
            // En: Initialise new game
            $grille = commencement();


            // Fr: Afficher le grille
            // En: Render game board
            dessinePlateau($grille);

            $partieTerminee = false;
            $nbCoup         = 0;


            while (!$partieTerminee && ($nbCoup < GRILLE_COLONNE * GRILLE_LIGNE)) {
                // Choix  - $colonne joueur
                $colonneJoue = -1;
                // mode_raw(1);
                $hauteurPion = -1;
                while ($hauteurPion == -1) {
                    while ($colonneJoue < 1 || $colonneJoue > 7) {
                        if (($nbCoup % 2) == 0) {
                            // En: RED turn to play
                            printf("\033[1;41;30m A %s en ROUGE de jouer\033[0m \n", $joueur1->nom);
                        } else {
                            // En: YELLOW turn to play
                            printf("\033[1;43;30m A %s JAUNE de jouer \033[0m \n", $joueur2->nom);
                        }

                        // Fr: Tapez un nombre entre 1 et 7 (numéro de colonne de gauche à droite) pour jouer
                        // En: Type a number between 1 and 7 (column number from left to right) to play
                        printf("Tapez une touche entre 1 et 7 :\n");
                        fscanf(STDIN, '%d', $colonneJoue);
                    }

                    // mode_raw(0);
                    $colonneJoue--;

                    // Fr: Joue le coup
                    // En: Play the ply
                    $hauteurPion = jouerCoup($grille, $colonneJoue, (($nbCoup % 2) == 0) ? CASE_ROUGE : CASE_JAUNE);
                    if ($hauteurPion == -1) {
                        // En: Invalid move
                        printf("Coup impossible!!!\n");
                    } else {
                        $nbCoup++;
                    }
                }

                // Fr: Affiche la grille
                // En: Render the game board
                dessinePlateau($grille);


                // Fr: La partie est-elle terminée ?
                // En: Is the game over?
                if (estPartieTerminee($grille, $nbCoup)) {
                    $partieTerminee = true;
                }
            }

            if (!$partieTerminee) {
                // En: Tie game / no winner
                printf('EGALITE');
                file_put_contents(
                    sprintf('puissance-4-main-egalite-nbcoup-%d-date-%s.txt', $nbCoup, $dateDebutPartie),
                    serialize($grille),
                );
            } else {
                if ($nbCoup % 2 == 0) {
                    // En: RED wins the game
                    printf("Les ROUGES ont gagnés\n");
                    $joueur1->win[$joueur2->niveau]++;
                    file_put_contents(
                        sprintf('puissance-4-main-joueur-1-gagne-nbcoup-%d-date-%s.txt', $nbCoup, $dateDebutPartie),
                        serialize($grille),
                    );
                } else {
                    // En: YELLOW wins the game
                    printf("Les JAUNES ont gagnés\n");
                    $joueur2->win[$joueur1->niveau]++;
                    file_put_contents(
                        sprintf('puissance-4-main-joueur-2-gagne-nbcoup-%d-date-%s.txt', $nbCoup, $dateDebutPartie),
                        serialize($grille),
                    );
                }
                $joueur1->nb_joue[$joueur2->niveau]++;
                $joueur2->nb_joue[$joueur1->niveau]++;
            }
// Demander si les joueurs veulent rejouer $i
// 79 N
// 78 O
            // En: Retry? Type the letter O if yes or the letter N if no
            printf("Rejouer (O/N)\n");
            while ($rejouer != 'O' && $rejouer != 'N') {
                fscanf(STDIN, '%s', $rejouer);
            }
        }
    } while ($rejouer != 'N');

    exit(0);
}

/**
 * Fr: Mode aléatoire (algorithme apprentissage automatique supervisé). Pareil qu'avant mais au lieu des humains ce sont des algos qui tournent
 * En: Random mode (Automated supervised learning algorithm). Basically everything is the same then normal run but with 2 automated gamers rather than humans
 *
 * @return void
 * @throws Exception
 */
function aleatoire(): void
{
    // En: How many games were played?
    $nbPartieJouees = 0;

    // En: Retry?
    $rejouer = 'O';

    do {
        // En: RED player 1 infos
        printf("Entrez les informations du joueur 1 (ROUGE) : \n");
        $joueur1 = nouveauJoueur('robot-1', 'D');
        printf("\n\n");

        // En: YELLOW player 2 infos
        printf("Entrez les informations du joueur 2 (JAUNE) : \n");
        $joueur2 = nouveauJoueur('robot-2', 'D');

        do {
            $dateDebutPartie = gmdate(DateTimeInterface::RFC3339);
            afficheStatistiquesJoueur($joueur1);
            afficheStatistiquesJoueur($joueur2);

            // Initialiser la nouvelle grille
            $grille = commencement();


            //Afficher le grille
            dessinePlateau($grille);

            $nbCoup         = 0;
            $partieTerminee = false;

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
                        try {
                            $colonneJoue = random_int(1, 7);
                        } catch (Throwable $colonneJoueAleatoireException) {
                            $colonneJoue = 4; // Jouer au centre par défaut en cas d'erreur
                        }
                    }

                    // mode_raw(0);
                    $colonneJoue--;

                    // Joue le coup
                    $pionActuel  = (($nbCoup % 2) == 0) ? CASE_ROUGE : CASE_JAUNE;
                    $hauteurPion = jouerCoup($grille, $colonneJoue, $pionActuel);
                    if ($hauteurPion == -1) {
                        printf("Coup impossible!!!\n");
                    } else {
                        // Fr: Sauvegarder état grille actuel dans un fichier texte. Pour pouvoir plus tard importer
                        // En: Save current board state in a text file. So we can later import it
                        file_put_contents(
                            sprintf(
                                'puissance-4-joueur-%d-actuel-nbcoup-%d-colonne-%d-date-%s.txt',
                                $pionActuel,
                                $nbCoup,
                                $colonneJoue,
                                $dateDebutPartie,
                            ),
                            serialize($grille),
                        );
                        $nbCoup++;
                    }
                }

                // Affiche la grille
                dessinePlateau($grille);


                // La partie est-elle terminée ?
                $partieTerminee = estPartieTerminee($grille, $nbCoup);
            }

            if ($nbPartieJouees < MAX_ECHANTILLONS) {
                ++$nbPartieJouees;
            } else {
                $rejouer = 'N';
                break;
            }
            if (!$partieTerminee) {
                printf('EGALITE');
                 ++$joueur1->win[$joueur2->niveau]; // En: Add point to RED player
                 ++$joueur2->win[$joueur1->niveau]; // En: Add point to YELLOW players
            } else {
                if ($nbCoup % 2 == 0) {
                    printf("Les ROUGES ont gagnés\n");
                    ++$joueur1->win[$joueur2->niveau];
                } else {
                    printf("Les JAUNES ont gagnés\n");
                    ++$joueur2->win[$joueur1->niveau];
                    file_put_contents(
                        sprintf(
                            'puissance-4-aleatoire-joueur-2-gagne-nbcoup-%d-date-%s.txt',
                            $nbCoup,
                            $dateDebutPartie,
                        ),
                        serialize($grille),
                    );
                }
                ++$joueur1->nb_joue[$joueur2->niveau];
                ++$joueur2->nb_joue[$joueur1->niveau];
            }

// Demander si les joueurs veulent rejouer $i
// 79 N
// 78 O
            printf("Rejouer (O/N)\n");

            // Fr: IMPORTANT: Le biais qui force l'algo à tendre vers une "excellence" tolérance 0 veut dire que le joueur 2 à GAGNÉ TOUTES les parties jouées.
            // En: IMPORTANT: This is the bias that forces the algo to tend to "excellence" tolerance 0 means that player 2 WON ALL the games it played
            $valeur  = ($joueur2->win[$joueur1->niveau] / $joueur2->nb_joue[$joueur1->niveau]);
            $rejouer = (
                (
                    ($nbPartieJouees >= MAX_ECHANTILLONS)
                    || ($nbCoup === NB_COUP_MINIMAL_JOUEUR_2_GAGNE)
                )
                && (
                    ((TOLERANCE >= 0.0) && (TOLERANCE <= 1.0))
                    && (
                        (($valeur * (1.0 - TOLERANCE)) <= $valeur)
                        && ($valeur <= (1.0 + TOLERANCE))
                    )
                )
            ) ? 'N' : 'O';
        } while ($rejouer == 'O');
        // Fr: Tant que le joueur 2 ne gagne pas recommencer sinon quitter
        // En: While player 2 lose a game retry, otherwise quit
    } while ($rejouer != 'N');

    afficheStatistiquesJoueur($joueur1);
    afficheStatistiquesJoueur($joueur2);

    exit(0);
}

// Fr: Si aucun mode de jeu choisi, faire le comportement par défaut
// En: If no game mode chosen. Run defaut routine
if (!($_SERVER['MODE_DE_JEU'] ?? false)) {
    main();

    return;
}

// Fr: Modes de jeu
// En: Game modes
switch ($_SERVER['MODE_DE_JEU']) {
    // Fr: Algo apprentissage supervisé optimisé pour que le joueur 2 gagne à Puissance 4 en un minimum de coups possibles
    // En: Supervised learning algo to maximise player 2 winning the game in Connect 4 in minimum plies possible
    case 'aleatoire':
        try {
            aleatoire();
        } catch (Exception $e) {
        }
        break;
    // Fr: Importer votre grille de jeu
    // En: Import your game board data
    case 'importer':
        echo 'Préciser la grille a importer grâce à la variable d\'environement GRILLE_JEU' . PHP_EOL;
        $fichier = (!empty($_SERVER['GRILLE_JEU']) && is_file($_SERVER['GRILLE_JEU']) && (filesize(
                    $_SERVER['GRILLE_JEU'],
                ) > 0)) ? $_SERVER['GRILLE_JEU'] : '';
        if (empty($fichier)) {
            echo 'Impossible importer partie. Fichier specifié a un problème ou n\'existe pas' . PHP_EOL;
            exit(1);
        }
        // Afficher grille importée ou grille vide du commencement de la partie
        dessinePlateau(unserialize(file_get_contents($fichier)) ?? commencement());
        break;
    // Fr: Mode par défaut
    // En: Default mode
    default:
        main();
}
