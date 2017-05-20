<?php
  /*
    * Id Club se utiliza en la url para indicar el equipo del que queremos obtener los datos
    * 3820589 => UVFA Aldosivi
    * 3401857 => Fonce FC
  */
  $id_club = 3820589;
  //Url con datos de los últimos 10 partidos. No hay datos de amistosos
  $url_games= 'https://www.easports.com/iframe/fifa17proclubs/api/platforms/PS4/clubs/'. $id_club .'/matches';
  //Url con datos de los miembros del club. No muestra datos de viejos miembros
  $url_members= 'https://www.easports.com/iframe/fifa17proclubs/api/platforms/PS4/clubs/'. $id_club .'/membersComplete';

  echo '<h2>MIEMBROS</h2>';
  //Obtiene e imprime nombre de miembros
  //Este listado es solo el de miembros actuales, por ende
  //tal vez no deberia usarse
  //Los miembros se irian cargando a medida que van jugando
  //es decir, al recorrer un partido nuevo
  //si un jugador no existe en base de datos
  //se lo inserta por primera vez
  //Si no jugo nunca no tiene sentido tenerlo en las estadisticas
  ini_set("allow_url_fopen", 1);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL, $url_members);
  $result = curl_exec($ch);
  curl_close($ch);
  $obj = json_decode($result);
  $rows = get_object_vars ($obj->raw);
  foreach ($rows as $row) {
    echo $row->name . '<br>';
  }

  //Ultimos partidos
  echo '<h2>ÚLTIMOS PARTIDOS</h2>';
  ini_set("allow_url_fopen", 1);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL, $url_games);
  $result = curl_exec($ch);
  curl_close($ch);
  $obj = json_decode($result);
  $games = get_object_vars ($obj->raw);

  //en este array vamos a guardar la info
  //Al final lo convertiremos a objeto
  $games_info = array();
  foreach ($games as $game) {
    //En este array vamos a guardar la info del partido
    $game_info = array('id' => 0, 'date_game'=>'', 'clubs'=>array());
    //id del partido
    $game_info['id'] = $game->matchId;
    //Timestamp del partido. Deberiamos convertirlo a fecha hora formato MySQL Y-m-d H:i:s
    $game_info['date_game'] = $game->timestamp;

    //Obtenemos los clubs que participaron del partido
    $clubs = get_object_vars ($game->clubs);
    //Recorremos los clubss
    foreach ($clubs as $club) {
      //En este array vamos a guardar la info del club
      $club_info = array('id' => 0, 'name'=>'',  'goals'=>0, 'players'=>array());

      $club_info['id'] = $club->details->clubId;
      $club_info['name'] = $club->details->name;
      $club_info['goals'] = $club->goals;
      //TODO
      $clubId=  $club->details->clubId;


      //Obtenemos los jugadores del club que participaron del partido
      $players = get_object_vars($game->players->{$clubId});

      //Recorremos los jugadores
      foreach ($players as $player) {
        //En este array vamos a guardar la info del jugador
        $player_info= array();
        $player_info['name']=  $player->playername;

        //Asistencias
        $player_info['assists']=  $player->assists;
        $player_info['cleansheetsany'] = $player->cleansheetsany;
        $player_info['cleansheetsdef'] = $player->cleansheetsdef;
        $player_info['cleansheetsgk'] = $player->cleansheetsgk;
        //Goles
        $player_info['goals'] = $player->goals;
        $player_info['goalsconceded'] = $player->goalsconceded;
        $player_info['losses'] = $player->losses;
        //Hombre del partido si es igual a 1
        $player_info['mom'] = $player->mom;
        //Intentos de pase
        $player_info['passattempts'] = $player->passattempts;
        //pases exitosos
        $player_info['passesmade'] = $player->passesmade;
        //2 defensa, 3 mediocampo, 4 atacante. Supongo que arquero es 1
        $player_info['pos'] = $player->pos;
        //Calificación post partido
        $player_info['rating'] = $player->rating;
        $player_info['realtimegame'] = $player->realtimegame;
        $player_info['realtimeidle'] = $player->realtimeidle;
        //tarjeta roja
        $player_info['redcards'] = $player->redcards;
        $player_info['saves'] = $player->saves;
        $player_info['SCORE'] = $player->SCORE;
        //Disparos realizados
        $player_info['shots'] = $player->shots;
        $player_info['tackleattempts'] = $player->tackleattempts;
        $player_info['tacklesmade'] = $player->tackleattempts;
        $player_info['vproattr'] = $player->vproattr;
        //Indica si gano o no el partido
        $player_info['wins'] = $player->wins;

        $club_info['players'][]= (object)$player_info;
      }
      $game_info['clubs'][]= (object)$club_info;
    }
    //Añadimos la info del partido al array de partidos
    $games_info[]= (object)$game_info ;
  }
  //Imprimimos algunos datos de prueba
  foreach ($games_info as $game_info) {
    //properties id, date_game ,clubs);
    echo '<b>PARTIDO ' . $game_info->id .'</b><br>';
    echo $game_info->clubs[0]->name . ' ' . $game_info->clubs[0]->goals;
    echo ' - ';
    echo $game_info->clubs[1]->goals . ' ' . $game_info->clubs[1]->name;
    echo '<br>';
    foreach ($game_info->clubs as $club) {
      //properties id, name, goals, players
      if($club->id==$id_club){
        //guardamos el resultado de ambos clubes, pero de los jugadores solo queremos saber los de nuestro equipo
        echo '<ul>';
        foreach ($club->players as $player) {
          echo '<li>';
          echo '<b>'.  $player->name .'</b><br>';
          echo '<ol>';
            echo 'Calificación: ' . $player->rating;
            echo ' || Goles: ' . $player->goals;
            echo ' || sistencias:' . $player->assists . '<br>';
          echo '</ol>';
          echo '</li>';
        }
        echo '</ul>';
      }
    }
    echo '<br><br>';
  }

//extract
//hay un tema a considerar, los jugadores a los que se les desconecta
//hay que analizarlo
//He notado que al parecer los califica con un 3.00
//No tengo idea de si es igual que se te desconecte al minuto 1 o al minuto 89
//Pero podriamos hacer algo Asi:
/*
  Si la calificacion es igual a 3.00 presuponemos que abandono
  pero ojo, capaz jugo todo y jugo re mal
  Identifiqué algunas valores que son claramente diferentes si se desconectó
  Las primeras cuatro son de un jugador que abandono
  las siguientes cuatro de uno que termino el partido
  podriamos hacer ubna segunda comparacion con esas tres propiedades
  si la tres son igual a cero consideramos que abandono y no guardamos las stats
  "rating"="3.00"=
  "realtimegame"="0"= ES CERO SI NO TERMINO
  "realtimeidle"="0"= ES CERO SI NO TERMINO
  "vproattr"="0"= ES CERO SI NO TERMINO

  "rating"="7.50"=
  "realtimegame"="12251"=
  "realtimeidle"="136"=
  "vproattr"="NH"=

  en el objeto player que usamos para imprimir en pantalla las propiedades son
  $player_info->rating
  $player_info->realtimegame
  $player_info->realtimeidle
  $player_info->vproattr



*/




?>
