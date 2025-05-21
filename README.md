# Puissance 4 en PHP

![visitor badge](https://visitor-badge.laobi.icu/badge?page_id=alexandreelise.puissance4-php&style=flat&format=true)
![GitHub followers](https://img.shields.io/github/followers/alexandreelise?style=flat)
![YouTube Channel Views](https://img.shields.io/youtube/channel/views/UCCya8rIL-PVHm8Mt4QPW-xw?style=flat&label=YouTube%20%40Api%20Adept%20vues)


<pre>

    __  __     ____         _____                              __                      __              
   / / / ___  / / ____     / ___/__  ______  ___  _____       / ____  ____  ____ ___  / ___  __________
  / /_/ / _ \/ / / __ \    \__ \/ / / / __ \/ _ \/ ___/  __  / / __ \/ __ \/ __ `__ \/ / _ \/ ___/ ___/
 / __  /  __/ / / /_/ /   ___/ / /_/ / /_/ /  __/ /     / /_/ / /_/ / /_/ / / / / / / /  __/ /  (__  ) 
/_/ /_/\___/_/_/\____/   /____/\__,_/ .___/\___/_/      \____/\____/\____/_/ /_/ /_/_/\___/_/  /____/  
                                   /_/                                                                 


</pre>

> ![GitHub Repo stars](https://img.shields.io/github/stars/alexandreelise/puissance4-php?style=flat) ![GitHub forks](https://img.shields.io/github/forks/alexandreelise/puissance4-php?style=flat) ![GitHub watchers](https://img.shields.io/github/watchers/alexandreelise/puissance4-php?style=flat)

----

En Français:

----

> Portage en PHP de la version du jeu Puissance 4 codé en C par un
> ami: [Yannick LAVALLIÈRE](https://github.com/LAVALLIERE) dans son
> projet https://github.com/LAVALLIERE/puissance4-c.git

## Pré-requis:

- Etre à l'aise avec les lignes de commandes et le terminal
- Avoir PHP 7.3 ou plus récent installé sur votre machine.
- Avoir Git installé pour pouvoir cloner le dépôt (optionnel car vous pouvez télécharger le fichier zip du code source)

## Instructions:

Dans votre terminal, tapez les commandes suivantes

```bash

git clone https://github.com/alexandreelise/puissance4-php.git \
 && cd puissance4-php \
 && php puissance4.php

``` 

Cela va exécuter le jeu en mode cli. Pour plus de modes de jeu, je vous invite à lire le code source. Vous y trouverez
des fonctionnalités cachées. ;-)

> MODES DE JEU :

- aleatoire
- web
- importer
- default

> MODE DE JEU : aleatoire
> Fr : Algo apprentissage supervisé optimisé pour que le joueur 2 gagne à Puissance 4 en un minimum de coups possibles

```

MODE_DE_JEU=aleatoire php puissance4.php

```

> GAME MODE : web
> Fr : Affichage en mode web pas encore implémenté

```

MODE_DE_JEU=web php puissance4.php

```

> GAME MODE : importer
> Fr : Importer votre grille de jeu

```

MODE_DE_JEU=importer GRILLE_JEU=import-jeu.txt php puissance4.php

```

> GAME MODE : default
> Fr : Par défaut

```

php puissance4.php

```

----

In English:

----

> A port in PHP of the Connect 4 game written in C by a friend: [Yannick LAVALLIÈRE](https://github.com/LAVALLIERE) in
> his project https://github.com/LAVALLIERE/puissance4-c.git

----

## Requirements:

- Have a good grasp of terminal. At least basic knowledge of command line usage.
- Have at least PHP version 7.3 or later installed on your machine
- Have Git installed to clone the repo (optional because you can download the zip package of the source code instead)

## Usage:

In your terminal, type the following commands

```bash

git clone https://github.com/alexandreelise/puissance4-php.git \
 && cd puissance4-php \
 && php puissance4.php

```

This will execute the cli game mode. For more game modes I invite you to read the code. You'll find hidden features. ;-)

> GAME MODES

- aleatoire
- web
- importer
- default

> GAME MODE : aleatoire
> En: Supervised learning algo to maximise player 2 winning the game in Connect 4 in minimum plies possible

```

MODE_DE_JEU=aleatoire php puissance4.php

```

> GAME MODE : web
> En: Render in a web page not implemented yet

```

MODE_DE_JEU=web php puissance4.php

```

> GAME MODE : importer
> En: Import your game board data

```

MODE_DE_JEU=importer GRILLE_JEU=game-import.txt php puissance4.php

```

> GAME MODE : default
> En: Default

```

php puissance4.php

```
