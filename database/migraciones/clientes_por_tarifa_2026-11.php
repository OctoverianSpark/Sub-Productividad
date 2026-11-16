<?php

/**
 * Tipo de tarifa de horas extra por cliente, segun
 * "Desglose de clientes activos por tarifa Noviembre 2026.docx".
 *
 *   tarifa_1 = Clientes viejos          (6 / 6 / 7 / 7,5)
 *   tarifa_2 = Nuevos tarifas 140 y 250 (6,5 / 7 / 7,5 / 8)
 *   tarifa_3 = Nuevos tarifas 160 y 270 (7 / 8 / 8 / 8,5)
 *
 * Los nombres se buscan en la tabla clientes sin importar mayusculas, tildes ni signos.
 * "/" separa nombres alternativos del mismo cliente (basta con que uno coincida).
 * Lo usa database/migrar_cliente_empleado.php para asignar el tipo a todas las relaciones del cliente.
 */
return [
    "tarifa_1" => [
        "Andres Wulff",
        "Anen Ybrahim / Luis Valles",
        "Angy Delgado",
        "Arleen Cuello",
        "Carlos Salaverria",
        "Daniel Blanco",
        "Daniel Oropeza",
        "Daniela Montes",
        "Edgar Gonzalez / Rafael Sanchez",
        "Falon Herrera",
        "Fernando Calles / Monica Benitez",
        "Fernando Leopold / Carla Leopold",
        "Isabel Mayz",
        "Jesus Lemus",
        "Jose Milhet",
        "Karen Nuñes / Joseph Nunes",
        "Lorena Chaurio",
        "Luis Aguero",
        "Mabel Enriquez",
        "Mario Suarez",
        "Nancy García",
        "Nolides González",
        "Odalmys Porres",
        "Orlando Ravelo",
        "Raniela Rivero",
        "Raquel Oropeza",
        "Raquel Vargas",
        "Raul Sanchez / Claudia Gomez",
        "Rigoberto Moreno",
        "Rosa Castro",
        "Zurama Fermin",
    ],
    "tarifa_2" => [
        "Geraldine Aliss",
        "Joraxy Bohorquez",
        "Yassel Zapata",
        "Marcos Lopez",
        "Flor Abou", // en el docx: "Flor Abou H."
        "Oliver Regalado",
        "Berta Herrera",
        "Elaine Carletti",
    ],
    "tarifa_3" => [
        "Daniela Colmenares",
        "Ariel Aguado",
        "Daniel Orraiz",
        "Dianelys Padron",
        "Monica Ramirez",
        // En el docx figura como "Malicare" ($715,40 + $1620 = 6 x 270); en el Excel de servicios es SURE PROTECTION
        "Malicare / Sure Protection",
    ],
];
