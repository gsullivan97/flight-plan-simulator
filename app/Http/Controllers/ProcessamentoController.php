<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Exception;

class ProcessamentoController extends Controller
{
    /**
     * Traz a lista de voos da API fornecida
     */
    public function getListaVoos()
    {
        $response = Http::get('http://prova.123milhas.net/api/flights');
        return $response->json();
    }

    /**
     * Traz o agrupamento de voos da API fornecida
     */
    public function getAgrupamentoVoos()
    {
        return $this->Agrupamento();
    }

    /**
     * Traz o agrupamento de voos da API fornecida em ordem decrescente
     */
    public function getAgrupamentoVoosByPreco()
    {
        $grupos = $this->Agrupamento();
        return $this->orderByPreco($grupos);
    }

    /**
     * Traz o agrupamento de voos da API fornecida junto com os dados requisitados na questão 4
     */
    public function getAgrupamentoVoosCompleto()
    {
        $grupos = $this->getAgrupamentoVoosByPreco();
        $flights = $this->getListaVoos();
        return [
            'flights' => $flights,
            'groups' => $grupos,
            'totalGroups' => count($grupos), // quantidade total de grupos
            'totalFlights' => count($flights), // quantidade total de voos únicos
            'cheapestPrice' => $grupos[0]['totalPrice'], // preço do grupo mais barato
            'cheapestGroup' => $grupos[0]['uniqueId'] // id único do grupo mais barato
        ];
    }

    /**
     * Faz o agrupamento dos voos usando array outbound e inbound
     */
    public function Agrupamento()
    {
        $ListaVoos = $this->getListaVoos();
        $grupos = [];
        $id = 1;
        
        for($i=0; $i<count($ListaVoos); $i++) {
            $outbounds[] = $this->Outbound($ListaVoos);
        }
        
        foreach($outbounds as $outbound) {
            if(!empty($outbound)) {
                $ListaInbound = $ListaVoos;
                $this->Inbound($ListaInbound, $outbound, $grupos, $id);
            }
        }   

        return $grupos;
    }

    public function Outbound(&$ListaVoos) 
    {
        $outbound = [];
        foreach($ListaVoos as $key => $aux) {
            if($aux['outbound']) {
                if (empty($outbound)) {
                    $outbound[] = $aux;
                    $voo = $aux;
                    unset($ListaVoos[$key]);
                } elseif($aux['fare'] == $voo['fare'] && $aux['price'] == $voo['price'] && ($aux['origin'] == $voo['origin'] && $aux['destination'] == $voo['destination']) && $aux['id'] != $voo['id'] ) {
                    $outbound[] = $aux;
                    unset($ListaVoos[$key]);
                }
            }
        }

        return $outbound;
    }

    public function Inbound(&$ListaVoos, $outbound, &$grupos, &$id) 
    {
        for($i=0; $i<count($ListaVoos); $i++) {
            $inbound = [];
            foreach($ListaVoos as $key => $aux) {
                if ($aux['inbound'] && $aux['fare'] == $outbound[0]['fare'] && ($aux['origin'] == $outbound[0]['destination'] && $aux['destination'] == $outbound[0]['origin'])) {
                    if (empty($inbound)) {
                        $inbound[] = $aux;
                        $totalPrice = $outbound[0]['price'] + $aux['price'];
                        unset($ListaVoos[$key]);
                    } else {
                        if ($totalPrice == ($outbound[0]['price'] + $aux['price'])){
                            $inbound[] = $aux;
                            unset($ListaVoos[$key]);
                        }
                    }
                }
            }
            
            if(!empty($outbound) && !empty($inbound)) {
                $grupos[] = [
                    'uniqueId' => $id,
                    'totalPrice' => $totalPrice,
                    'outbound' => $outbound,
                    'inbound' => $inbound
                ];
                $id++;
            }
        }
    }

    /**
     * Função para definir o metodo de ordenação utilizado
     */
    public function orderByPreco($grupos) 
    {
        usort($grupos,[$this,'MenorPreco']);
        return $grupos;
    }

    /**
     * Função para definir os parametros usados na ordenação
     */
    public static function MenorPreco($grupoA, $grupoB) 
    {
        if ($grupoA['totalPrice'] == $grupoB['totalPrice']) {
            return 0;
        }
        return ($grupoA['totalPrice'] < $grupoB['totalPrice']) ? -1 : 1;
    }
}