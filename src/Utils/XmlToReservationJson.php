<?php

namespace App\Utils;

class XmlToReservationJson
{
    /**
     * Mapea el array generado desde el XML a la estructura JSON final esperada.
     * @param array $xmlArray
     * @return array
     */
    public static function map(array $xmlArray): array
    {
        // Ejemplo de mapeo básico, debes adaptar los campos según el XML real
        $root = $xmlArray['ROOT'] ?? $xmlArray;
        $clave = $root['Clave'] ?? [];
        $cliente = $root['Cliente'] ?? [];
        $estancia = $root['Estancia'] ?? [];
        $personas = $root['PersonasEstancia']['Persona'] ?? [];
        if (isset($personas['Numero'])) {
            $personas = [$personas]; // Si solo hay una persona
        }
        // Mapeo de ejemplo, debes adaptar los campos reales
        return [
            'reservationId' => $clave['NumeroEstancia'] ?? null,
            'totalPrice' => $estancia['Importes']['Estancia'] ?? null,
            'currency' => 'EUR',
            'partialPaymentsAllowed' => true,
            'reservationHolder' => [
                'reservationHolderId' => $cliente['Numero'] ?? null,
                'name' => $cliente['Nombre'] ?? null,
                'lastName1' => $cliente['Apellidos'] ?? null,
                'lastName2' => null,
                'isACompany' => false
            ],
            'reservationComponent' => [
                [
                    'arrivalDateTime' => isset($estancia['Del']) ? str_replace(['/', '-'], '', $estancia['Del']) . 'T1600' : null,
                    'departureDateTime' => isset($estancia['AL']) ? str_replace(['/', '-'], '', $estancia['AL']) . 'T1200' : null,
                    'numberOfAdults' => self::countByRubrica($estancia, 1),
                    'numberOfChildren' => self::countByRubrica($estancia, 2),
                    'notes_remarks' => null,
                    'price' => $estancia['Importes']['Estancia'] ?? null,
                    'deposit' => $estancia['Importes']['Pagado'] ?? null,
                    'currency' => 'EUR',
                    'boardTypeId' => $estancia['IdTarifa'] ?? null,
                    'boardTypeName' => $estancia['Tarifa'] ?? null,
                    'room' => [
                        'roomId' => $estancia['Numero'] ?? null,
                        'roomName' => $estancia['Emplazamiento']['Empla']['Numero'] ?? null,
                        'roomType' => [
                            'roomTypeId' => $estancia['Emplazamiento']['Empla']['Categoria'] ?? null,
                            'roomTypeName' => $estancia['Emplazamiento']['Empla']['Categoria'] ?? null,
                            'maxOccupancy' => $estancia['TotalPersonas'] ?? null,
                            'description' => null,
                            'isAvailable' => true
                        ]
                    ],
                    'guest' => self::mapGuests($personas)
                ]
            ]
        ];
    }

    private static function countByRubrica($estancia, $codigo)
    {
        if (!isset($estancia['Rubricas']['Rubrica'])) return 0;
        $rubricas = $estancia['Rubricas']['Rubrica'];
        if (isset($rubricas['Codigo'])) $rubricas = [$rubricas];
        foreach ($rubricas as $rubrica) {
            if ((int)$rubrica['Codigo'] === $codigo) {
                return (int)$rubrica['Cantidad'];
            }
        }
        return 0;
    }

    private static function mapGuests($personas)
    {
        $guests = [];
        foreach ($personas as $persona) {
            $guests[] = [
                'guestId' => $persona['Numero'] ?? null,
                'loyaltyId' => null,
                'firstName' => $persona['Nombre'] ?? null,
                'lastName1' => $persona['Apellidos'] ?? null,
                'lastName2' => null,
                'nationality' => $persona['Nacionalidad']['Nombre'] ?? null,
                'birthdate' => isset($persona['Fechanacimiento']) ? str_replace('/', '', $persona['Fechanacimiento']) : null,
                'gender' => isset($persona['Sexo']) ? ($persona['Sexo'] == 1 ? 'M' : 'F') : null,
                'idTypeName' => $persona['DNI']['Tipo'] ?? null,
                'idNumber' => $persona['DNI']['Numero'] ?? null,
                'idIssueDate' => $persona['DNI']['FechaExpedicion'] ?? null,
                'idExpirationDate' => $persona['DNI']['FechaCaducidad'] ?? null,
                'landline' => $persona['Telefono1'] ?? null,
                'mobile' => $persona['Telefono2'] ?? null,
                'email' => $persona['Email'] ?? null,
                'street' => $persona['Direccion']['Direccion1'] ?? null,
                'city' => $persona['Direccion']['Poblacion'] ?? null,
                'stateOrProvince' => $persona['Direccion']['Provincia'] ?? null,
                'country' => $persona['Direccion']['Pais']['Nombre'] ?? null,
                'zipCode' => $persona['Direccion']['CodigoPostal'] ?? null
            ];
        }
        return $guests;
    }
}
