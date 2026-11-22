<?php
namespace Hmb\Component\Quiz\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Páginas de entrada SEO del Metal Quiz (/metal-quiz/<slug>). Contenido propio y comprobable:
 * cada pregunta de ejemplo es un dato histórico verificable. Las bandas enlazadas se buscan por
 * nombre en la tabla de bandas; si alguna no existe, simplemente no se enlaza.
 */
class TemasHelper
{
	public static function all(): array
	{
		return [
			'test-de-metal-para-expertos' => [
				'nombre' => 'Test de metal para expertos',
				'title'  => 'Test de metal para expertos: ¿cuánto sabes de verdad?',
				'desc'   => 'Test de metal para expertos: preguntas difíciles sobre bandas, discos, formaciones y años del heavy metal. Juega gratis y comprueba si eres un metalhead de verdad.',
				'h1'     => 'Test de metal para expertos',
				'intro'  => [
					'¿Te sabes de memoria los discos de Judas Priest, quién tocaba el bajo en la primera formación de Metallica o en qué ciudad nació cada banda de la NWOBHM? Este test de metal está pensado para quienes ya han superado el nivel básico y quieren una prueba a la altura.',
					'En el Metal Quiz de Heavy Metal Brothers pones a prueba bandas, discos, años, miembros y efemérides, con rachas, comodines y ranking para que cada partida cuente. Es gratis y puedes jugar sin registrarte. Abajo tienes unas preguntas de ejemplo de nivel experto para calentar.',
				],
				'preguntas' => [
					['¿Qué batería grabó «Reign in Blood» (1986) con Slayer?', 'Dave Lombardo'],
					['¿En qué ciudad británica se formó Black Sabbath?', 'Birmingham'],
					['¿Con qué banda grabó Cliff Burton «Master of Puppets» (1986)?', 'Metallica'],
					['¿Qué álbum de Judas Priest se publicó en 1990 con una portada de un jinete metálico?', 'Painkiller'],
				],
				'bandas' => ['Judas Priest', 'Black Sabbath', 'Metallica', 'Slayer', 'Iron Maiden'],
			],
			'quiz-iron-maiden' => [
				'nombre' => 'Quiz de Iron Maiden',
				'title'  => 'Quiz de Iron Maiden: preguntas sobre la Doncella de Hierro',
				'desc'   => 'Quiz de Iron Maiden online: preguntas sobre Eddie, los discos, los cantantes y la historia de la banda de Steve Harris. ¿Eres un verdadero Maiden fan? Juega gratis.',
				'h1'     => 'Quiz de Iron Maiden',
				'intro'  => [
					'Eddie, el bajo de Steve Harris, los galopes de guitarra, el «Scream for me»… Iron Maiden es una de las bandas que más fans de por vida ha generado en el heavy metal. Este quiz de Iron Maiden pone a prueba cuánto conoces de verdad a la Doncella de Hierro: discos, cantantes, canciones y años clave.',
					'Entra en el Metal Quiz y demuestra lo que sabes de heavy metal: bandas, discos, años y miembros. Cada respuesta correcta suma puntos y racha, y puedes ver tu posición en el ranking. Abajo tienes cuatro preguntas de ejemplo sobre Iron Maiden.',
				],
				'preguntas' => [
					['¿Cómo se llama la mascota de Iron Maiden?', 'Eddie'],
					['¿Quién fundó Iron Maiden en Londres en 1975?', 'Steve Harris'],
					['¿Qué cantante grabó «The Number of the Beast» (1982), su primer disco con la banda?', 'Bruce Dickinson'],
					['¿Qué álbum de 1984 incluye «Aces High» y «2 Minutes to Midnight»?', 'Powerslave'],
				],
				'bandas' => ['Iron Maiden', 'Judas Priest', 'Saxon', 'Dio', 'Black Sabbath'],
			],
			'metal-trivia' => [
				'nombre' => 'Metal trivia',
				'title'  => 'Metal trivia: preguntas y respuestas de heavy metal',
				'desc'   => 'Metal trivia en español: preguntas y respuestas sobre bandas, discos, músicos y curiosidades del heavy metal. Juega al quiz gratis y compite en el ranking.',
				'h1'     => 'Metal trivia: preguntas de heavy metal',
				'intro'  => [
					'Si buscas metal trivia en español, aquí tienes un juego de preguntas y respuestas sobre el género: bandas, discos, músicos, años de formación, países de origen y efemérides. Desde los gigantes del thrash hasta bandas de culto del metal extremo.',
					'Cada ronda mezcla preguntas fáciles y difíciles, con comodines y rachas. Es el Metal Quiz de Heavy Metal Brothers: gratis, sin instalar nada y pensado para jugar desde el móvil. Abajo tienes unas preguntas de ejemplo para calentar y, si quieres, el enlace directo para empezar a jugar.',
				],
				'preguntas' => [
					['¿Cómo se llamaba el cantante de Motörhead, fallecido en 2015?', 'Lemmy Kilmister'],
					['¿Qué vocalista sustituyó a Ozzy Osbourne en Black Sabbath en 1979?', 'Ronnie James Dio'],
					['¿Qué banda publicó «British Steel» en 1980?', 'Judas Priest'],
					['¿En qué país nació Sepultura?', 'Brasil (Belo Horizonte)'],
				],
				'bandas' => ['Motörhead', 'Sepultura', 'Judas Priest', 'Black Sabbath', 'Dio'],
			],
			'quiz-heavy-metal-clasico' => [
				'nombre' => 'Quiz de heavy metal clásico',
				'title'  => 'Quiz de heavy metal clásico: los 70 y los 80',
				'desc'   => 'Quiz de heavy metal clásico: Black Sabbath, Deep Purple, Judas Priest, Motörhead, Dio y la NWOBHM. Preguntas sobre discos y bandas de los 70 y 80. Juega gratis.',
				'h1'     => 'Quiz de heavy metal clásico',
				'intro'  => [
					'Del primer disco de Black Sabbath en 1970 a la explosión de la NWOBHM y el metal de los ochenta: el heavy metal clásico es la base de todo lo que vino después. Este quiz de heavy metal clásico repasa las bandas, los discos y los himnos que definieron el sonido.',
					'Aquí tienes unas preguntas de ejemplo sobre Black Sabbath, Deep Purple, Judas Priest y Motörhead para comprobar si conoces la historia como crees. Juegas gratis en el Metal Quiz, sin registrarte.',
				],
				'preguntas' => [
					['¿En qué año publicó Black Sabbath su primer disco homónimo?', '1970'],
					['¿Qué banda grabó «Smoke on the Water»?', 'Deep Purple'],
					['¿Qué disco de Motörhead (1980) incluye «Ace of Spades»?', 'Ace of Spades'],
					['¿Quién es el cantante de Judas Priest en «British Steel»?', 'Rob Halford'],
				],
				'bandas' => ['Black Sabbath', 'Deep Purple', 'Judas Priest', 'Motörhead', 'Dio', 'Saxon'],
			],
			'quiz-thrash-metal' => [
				'nombre' => 'Quiz de thrash metal',
				'title'  => 'Quiz de thrash metal: Metallica, Slayer, Megadeth y más',
				'desc'   => 'Quiz de thrash metal: preguntas sobre Metallica, Slayer, Megadeth, Anthrax, Kreator o Sepultura. Mide cuánto sabes del género más rápido del metal. Juega gratis.',
				'h1'     => 'Quiz de thrash metal',
				'intro'  => [
					'Velocidad, riffs cortantes y letras sin concesiones: el thrash metal nació en los años ochenta y dejó nombres imprescindibles. Este quiz de thrash metal te pregunta por los «Cuatro Grandes» (Metallica, Megadeth, Slayer y Anthrax), por la escena alemana de Kreator o Sodom y por el thrash brasileño de Sepultura.',
					'Juega en el Metal Quiz y demuestra si distingues «Reign in Blood» de «Rust in Peace». Hay preguntas sobre discos, años, miembros y curiosidades, con rachas, comodines y ranking. Es gratis y funciona desde el móvil.',
				],
				'preguntas' => [
					['¿Qué banda fundó Dave Mustaine tras salir de Metallica?', 'Megadeth'],
					['¿Qué banda alemana grabó «Pleasure to Kill» (1986)?', 'Kreator'],
					['¿Cuáles son los «Cuatro Grandes» del thrash?', 'Metallica, Megadeth, Slayer y Anthrax'],
					['¿Qué disco de Slayer se publicó en 1986?', 'Reign in Blood'],
				],
				'bandas' => ['Metallica', 'Megadeth', 'Slayer', 'Anthrax', 'Kreator', 'Sepultura'],
			],
		];
	}

	public static function get(string $slug): ?array
	{
		$all = self::all();

		return $all[$slug] ?? null;
	}
}
