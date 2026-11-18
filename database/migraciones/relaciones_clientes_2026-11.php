<?php

/**
 * Empleados asignados a cada cliente activo, segun el Excel de ClickUp "CLIENTES CONTRATADOS"
 * (columna "Ejecutivo asignado", exportado el 18/11/2026). Solo la relacion; los precios no se usan.
 *
 * El tipo de cada relacion es el del cliente en clientes_por_tarifa_2026-11.php (o el tipo por defecto).
 * Los nombres se buscan sin importar mayusculas, tildes ni signos; basta con que todas las palabras
 * esten en el nombre registrado (ej: "Javier Gutierrez" encuentra "Javier Alejandro Gutierrez Saavedra").
 * "/" separa nombres alternativos de la misma persona o del mismo cliente.
 * Lo usa database/migrar_cliente_empleado.php (paso 3c).
 */
return [
    "Andres Wulff" => ["Pedro Cordova"],
    "Anen Ybrahim / Luis Valles" => ["Vicmary Gonzalez"],
    "Angy Delgado" => ["Jenniree Garcia / Jeniree Garcia"],
    "Ariel Aguado" => ["Ana Cahuao"],
    "Arleen Cuello" => ["Victor Alayon", "Douleidy Key"],
    "Berta Herrera" => ["Joselin Garcia", "Jenniree Garcia / Jeniree Garcia"],
    "Carlos Salaverria" => ["Zulay Mendez"],
    "Daniel Blanco" => ["Crissel Crespo"],
    "Daniel Oropeza" => ["Freiner Araujo"],
    "Daniel Orraiz" => ["Jennifer Petit"],
    "Daniela Colmenares" => ["Dayanna Martinez"],
    "Daniela Montes" => ["Abrannys Romero"],
    "Dianelys Padron" => ["Angel Rivero"],
    "Edgar Gonzalez / Rafael Sanchez" => ["Luis Montilla"],
    "Elaine Carletti" => ["Mayra Parra", "Carolina Torres"],
    "Falon Herrera" => ["Yaniant Rovira"],
    "Fernando Calles / Monica Benitez" => ["Jenesis Gotopo / Genesis Gotopo", "Vicente Bautista"],
    "Fernando Leopold / Carla Leopold" => ["Maria Suarez", "Juraima Bracho"],
    "Flor Abou" => ["Jesus Salazar", "Genesis Marquez"],
    "Geraldine Aliss" => ["Ariackny Montilla"],
    "Isabel Mayz" => ["Rosalba Wilthen", "Tatiana Rosales", "Javier Gutierrez"],
    "Jesus Lemus" => ["Victor Gonzalez", "Jose Leal", "Jesus Guerrero", "Claudia Marquez", "Nicolas Bueno / Nicolas Alvarado"],
    "Joraxy Bohorquez" => ["Jessika Sanchez"],
    "Jose Milhet" => ["Emmanuel Hoyos"],
    "Karen Nuñes / Joseph Nunes" => ["Sol Cruz", "Ingrid Mantilla", "Indrani Zerpa", "Jessika Sanchez", "Lina Daza"],
    "Lorena Chaurio" => ["Adrian Gonzalez", "Maria Medina"],
    "Luis Aguero" => ["Wilfredo Marin", "Gabriel Gil"],
    "Mabel Enriquez" => ["Roman Espinoza", "Eliana Coronado", "Valentina Guarenas"],
    // En el Excel: 4 ejecutivos en Venezuela + 2 asistentes y un Quality Manager en Colombia
    "Malicare / Sure Protection" => ["Ysamar Balza", "Dugleisis Montenegro", "Maikol Gonzalez", "Jorge Urribarri", "Samantha Urdaneta", "David Gutierrez", "Michael Bolivar"],
    "Marcos Lopez" => ["Ariackny Montilla"],
    "Mario Suarez" => ["Michelle Soteldo"],
    "Monica Ramirez" => ["Yibelly Gonzalez"],
    "Nancy García" => ["Geraldine Gonzalez", "Geraldin Lezama"],
    "Nolides González" => ["Angie Herrera"],
    "Odalmys Porres" => ["Estefany Marin"],
    "Oliver Regalado" => ["Dalexandro Oberto", "William Rivero"],
    "Orlando Ravelo" => ["Andrea Rincon", "Carlos Amais"],
    "Raniela Rivero" => ["Valentina Guarenas"],
    "Raquel Oropeza" => ["Jose Almera"],
    "Raquel Vargas" => ["Jesus Arteaga", "Francelis Sanchez"],
    "Raul Sanchez / Claudia Gomez" => ["Andres Rivero", "Jhoselin Griman"],
    "Rigoberto Moreno" => ["Paola Peña"],
    "Rosa Castro" => ["Wilfredo Marin", "Thaimara Zerpa", "Dayanna Martinez"],
    "Yassel Zapata" => ["Andrea Rincon"],
    "Zurama Fermin" => ["Norelys Parra"],
];
