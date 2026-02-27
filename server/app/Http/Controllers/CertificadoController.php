<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Services\Sankhya\SankhyaDbExplorerSPService;

class CertificadoController extends Controller {
    private function hexToBase64(?string $hex): ?string
    {
        if (empty($hex)) {
            return null;
        }
        
        $hex = preg_replace('/\s+/', '', $hex);
        $hex = str_replace('0x', '', $hex);
        
        $binary = hex2bin($hex);
        
        if ($binary === false) {
            return null;
        }
        
        return base64_encode($binary);
    }

    public function getCertificadoByIdOS(Request $request)
    {
        $idOS = (int) $request->query('idOS');

        $sql = "
        SELECT
        OSE.NUMOS AS idOs,
        OSE.NUMOSNIKKEY AS numOs,

        OSE.CODPARC,
        PAR.RAZAOSOCIAL AS PrazaoSocial,     
        PAR.NOMEPARC AS PnomeFantasia,
        CASE 
            WHEN PAR.TIPPESSOA = 'J'
            THEN STUFF(
                    STUFF(
                        STUFF(
                            STUFF(PAR.CGC_CPF, 3, 0, '.'),
                        7, 0, '.'),
                    11, 0, '/'),
                16, 0, '-')         
            ELSE STUFF(
                    STUFF(
                        STUFF(PAR.CGC_CPF, 4, 0, '.'),
                    8, 0, '.'),
                12, 0, '-')
        END AS PcpfCnpj,
        CONCAT(PEN.TIPO,' ',PEN.NOMEEND) AS Plogradouro,
        PAR.NUMEND AS Pnumero,
        PBA.NOMEBAI AS Pbairro,
        PCI.NOMECID AS Pcidade,
        PAR.COMPLEMENTO AS Pcomplemento,
        STUFF(PAR.CEP, 6, 0, '-') AS Pcep,
        PUF.UF AS Pestado,

        NULL AS validadeContrato,
        NULL AS licencaFuncionamento,
        NULL AS dataValidadeLicenca,
        CONVERT(VARCHAR(10), ITE.AD_DT_GARRANTIA, 103) AS dataInicioValidadeCertificado,
        CONVERT(VARCHAR(10), DATEADD(DAY, ITE.AD_DIASGARANTIA, ITE.AD_DT_GARRANTIA), 103) AS dataFimValidadeCertificado,

        EMP.RAZAOSOCIAL AS razaoSocial,
        EMP.NOMEFANTASIA AS nomeFantasia,
        STUFF(
            STUFF(
                STUFF(
                    STUFF(EMP.CGC, 3, 0, '.'),
                7, 0, '.'),
            11, 0, '/'),
        16, 0, '-') AS cpfCnpj,
        CONCAT(EDE.TIPO,' ',EDE.NOMEEND) AS logradouro,
        EMP.NUMEND AS numero,
        BAI.NOMEBAI AS bairro,
        CID.NOMECID AS cidade,
        EMP.COMPLEMENTO AS complemento,
        STUFF(EMP.CEP, 6, 0, '-') AS cep,
        UFS.UF AS estado,
        EMP.TELEFONE AS telefone,

        VEN.APELIDO AS TrazaoSocial,
        VEN.APELIDO AS TnomeFantasia,
        NULL AS cpfCnpj,
        CASE
            WHEN VEN.AD_REGICREA IS NOT NULL
            THEN CASE
                    WHEN VEN.AD_CRBIO IS NOT NULL
                    THEN 'CREA/CRBIO'
                    ELSE 'CREA'
                END
            ELSE CASE
                    WHEN VEN.AD_CRBIO IS NOT NULL
                    THEN 'CRBIO'
                    ELSE NULL
                END
        END AS nomeConselho,
        CASE
            WHEN VEN.AD_REGICREA IS NOT NULL
            THEN CASE
                    WHEN VEN.AD_CRBIO IS NOT NULL
                    THEN CONCAT(VEN.AD_REGICREA,'/',VEN.AD_CRBIO)
                    ELSE VEN.AD_REGICREA
                END
            ELSE CASE
                    WHEN VEN.AD_CRBIO IS NOT NULL
                    THEN VEN.AD_CRBIO
                    ELSE NULL
                END
        END AS numeroConselho,
        TUS.AD_ASSINATURA AS imagemAssinatura
        FROM sankhya.AD_VGFOSE OSE
        INNER JOIN sankhya.TCSOSE TOS
            ON TOS.NUMOS = OSE.NUMOS 
        INNER JOIN sankhya.TGFCAB CAB
            ON CAB.NUNOTA = TOS.NUNOTA
        INNER JOIN sankhya.TGFITE ITE
            ON ITE.NUNOTA = CAB.NUNOTA
            AND ITE.CODPROD = (
                SELECT TOP 1 I.CODPROD
                FROM sankhya.TGFITE I
                INNER JOIN sankhya.TGFPRO P
                    ON P.CODPROD = I.CODPROD
                    AND P.AD_APP = 'S'
                WHERE I.NUNOTA = ITE.NUNOTA
                AND I.AD_DT_GARRANTIA IS NOT NULL
            )

            INNER JOIN sankhya.TSIEMP EMP
            ON EMP.CODEMP = CAB.CODEMP
        INNER JOIN sankhya.TSIEND EDE
            ON EDE.CODEND = EMP.CODEND
        INNER JOIN sankhya.TSIBAI BAI
            ON BAI.CODBAI = EMP.CODBAI
        INNER JOIN sankhya.TSICID CID
            ON CID.CODCID = EMP.CODCID
        INNER JOIN sankhya.TSIUFS UFS
            ON UFS.CODUF = CID.UF

            INNER JOIN sankhya.TGFPAR PAR
            ON PAR.CODPARC = OSE.CODPARC
        INNER JOIN sankhya.TSIEND PEN
            ON PEN.CODEND = PAR.CODEND
        INNER JOIN sankhya.TSIBAI PBA
            ON PBA.CODBAI = PAR.CODBAI
        INNER JOIN sankhya.TSICID PCI
            ON PCI.CODCID = PAR.CODCID
        INNER JOIN sankhya.TSIUFS PUF
            ON PUF.CODUF = PCI.UF

            LEFT JOIN sankhya.TGFVEN VEN
            ON VEN.CODVEND = CAB.AD_CODVEND
        LEFT JOIN sankhya.TSIUSU TUS
            ON TUS.CODVEND = VEN.CODVEND
        WHERE OSE.NUMOS = '{$idOs}'
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $item = $service->mapDbExplorerResult($result)[0];

        $formatted = [
            'idOs' => $item['idOs'] ?? null,
            'numOs' => $item['numOs'] ?? null,
            'parceiro' => [
                'codigo' => $item['CODPARC'] ?? null,
                'razaoSocial' => $item['PrazaoSocial'] ?? null,
                'nomeFantasia' => $item['PnomeFantasia'] ?? null,
                'cpfCnpj' => $item['PcpfCnpj'] ?? null,
                'logradouro' => $item['Plogradouro'] ?? null,
                'numero' => $item['Pnumero'] ?? null,
                'bairro' => $item['Pbairro'] ?? null,
                'cidade' => $item['Pcidade'] ?? null,
                'complemento' => $item['Pcomplemento'] ?? null,
                'cep' => $item['Pcep'] ?? null,
                'estado' => $item['Pestado'] ?? null,
            ],
            'certificado' => [
                'validadeContrato' => $item['validadeContrato'] ?? null,
                'licencaFuncionamento' => $item['licencaFuncionamento'] ?? null,
                'dataValidadeLicenca' => $item['dataValidadeLicenca'] ?? null,
                'dataInicioValidade' => $item['dataInicioValidadeCertificado'] ?? null,
                'dataFimValidade' => $item['dataFimValidadeCertificado'] ?? null,
            ],
            'nikkey' => [
                'razaoSocial' => $item['razaoSocial'] ?? null,
                'nomeFantasia' => $item['nomeFantasia'] ?? null,
                'cpfCnpj' => $item['cpfCnpj'] ?? null,
                'logradouro' => $item['logradouro'] ?? null,
                'numero' => $item['numero'] ?? null,
                'bairro' => $item['bairro'] ?? null,
                'cidade' => $item['cidade'] ?? null,
                'complemento' => $item['complemento'] ?? null,
                'cep' => $item['cep'] ?? null,
                'estado' => $item['estado'] ?? null,
                'telefone' => $item['telefone'] ?? null,
            ],
            'responsavelTecnico' => [
                'razaoSocial' => $item['TrazaoSocial'] ?? null,
                'nomeFantasia' => $item['TnomeFantasia'] ?? null,
                'cpfCnpj' => $item['cpfCnpj'] ?? null,
                'nomeConselho' => $item['nomeConselho'] ?? null,
                'numeroConselho' => $item['numeroConselho'] ?? null,
                'imagemAssinatura' => $this->hexToBase64($item['imagemAssinatura'] ?? null),
            ]
        ];

        return response()->json($formatted ?? null);
    }

    public function getCertificados(Request $request)
    {
        $idCliente = (int) $request->query('idCliente');

        $sql = "
        SELECT
        OSE.NUMOS AS idOs,
        OSE.NUMOSNIKKEY AS numOs,

        OSE.CODPARC,
        PAR.RAZAOSOCIAL AS PrazaoSocial,     
        PAR.NOMEPARC AS PnomeFantasia,
        CASE 
            WHEN PAR.TIPPESSOA = 'J'
            THEN STUFF(
                    STUFF(
                        STUFF(
                            STUFF(PAR.CGC_CPF, 3, 0, '.'),
                        7, 0, '.'),
                    11, 0, '/'),
                16, 0, '-')         
            ELSE STUFF(
                    STUFF(
                        STUFF(PAR.CGC_CPF, 4, 0, '.'),
                    8, 0, '.'),
                12, 0, '-')
        END AS PcpfCnpj,
        CONCAT(PEN.TIPO,' ',PEN.NOMEEND) AS Plogradouro,
        PAR.NUMEND AS Pnumero,
        PBA.NOMEBAI AS Pbairro,
        PCI.NOMECID AS Pcidade,
        PAR.COMPLEMENTO AS Pcomplemento,
        STUFF(PAR.CEP, 6, 0, '-') AS Pcep,
        PUF.UF AS Pestado,

        NULL AS validadeContrato,
        NULL AS licencaFuncionamento,
        NULL AS dataValidadeLicenca,
        CONVERT(VARCHAR(10), ITE.AD_DT_GARRANTIA, 103) AS dataInicioValidadeCertificado,
        CONVERT(VARCHAR(10), DATEADD(DAY, ITE.AD_DIASGARANTIA, ITE.AD_DT_GARRANTIA), 103) AS dataFimValidadeCertificado,

        EMP.RAZAOSOCIAL AS razaoSocial,
        EMP.NOMEFANTASIA AS nomeFantasia,
        STUFF(
            STUFF(
                STUFF(
                    STUFF(EMP.CGC, 3, 0, '.'),
                7, 0, '.'),
            11, 0, '/'),
        16, 0, '-') AS cpfCnpj,
        CONCAT(EDE.TIPO,' ',EDE.NOMEEND) AS logradouro,
        EMP.NUMEND AS numero,
        BAI.NOMEBAI AS bairro,
        CID.NOMECID AS cidade,
        EMP.COMPLEMENTO AS complemento,
        STUFF(EMP.CEP, 6, 0, '-') AS cep,
        UFS.UF AS estado,
        EMP.TELEFONE AS telefone,

        VEN.APELIDO AS TrazaoSocial,
        VEN.APELIDO AS TnomeFantasia,
        NULL AS cpfCnpj,
        CASE
            WHEN VEN.AD_REGICREA IS NOT NULL
            THEN CASE
                    WHEN VEN.AD_CRBIO IS NOT NULL
                    THEN 'CREA/CRBIO'
                    ELSE 'CREA'
                END
            ELSE CASE
                    WHEN VEN.AD_CRBIO IS NOT NULL
                    THEN 'CRBIO'
                    ELSE NULL
                END
        END AS nomeConselho,
        CASE
            WHEN VEN.AD_REGICREA IS NOT NULL
            THEN CASE
                    WHEN VEN.AD_CRBIO IS NOT NULL
                    THEN CONCAT(VEN.AD_REGICREA,'/',VEN.AD_CRBIO)
                    ELSE VEN.AD_REGICREA
                END
            ELSE CASE
                    WHEN VEN.AD_CRBIO IS NOT NULL
                    THEN VEN.AD_CRBIO
                    ELSE NULL
                END
        END AS numeroConselho,
        TUS.AD_ASSINATURA AS imagemAssinatura
        FROM sankhya.AD_VGFOSE OSE
        INNER JOIN sankhya.TCSOSE TOS
            ON TOS.NUMOS = OSE.NUMOS 
        INNER JOIN sankhya.TGFCAB CAB
            ON CAB.NUNOTA = TOS.NUNOTA
        INNER JOIN sankhya.TGFITE ITE
            ON ITE.NUNOTA = CAB.NUNOTA
            AND ITE.CODPROD = (
                SELECT TOP 1 I.CODPROD
                FROM sankhya.TGFITE I
                INNER JOIN sankhya.TGFPRO P
                    ON P.CODPROD = I.CODPROD
                    AND P.AD_APP = 'S'
                WHERE I.NUNOTA = ITE.NUNOTA
                AND I.AD_DT_GARRANTIA IS NOT NULL
            )

            INNER JOIN sankhya.TSIEMP EMP
            ON EMP.CODEMP = CAB.CODEMP
        INNER JOIN sankhya.TSIEND EDE
            ON EDE.CODEND = EMP.CODEND
        INNER JOIN sankhya.TSIBAI BAI
            ON BAI.CODBAI = EMP.CODBAI
        INNER JOIN sankhya.TSICID CID
            ON CID.CODCID = EMP.CODCID
        INNER JOIN sankhya.TSIUFS UFS
            ON UFS.CODUF = CID.UF

            INNER JOIN sankhya.TGFPAR PAR
            ON PAR.CODPARC = OSE.CODPARC
        INNER JOIN sankhya.TSIEND PEN
            ON PEN.CODEND = PAR.CODEND
        INNER JOIN sankhya.TSIBAI PBA
            ON PBA.CODBAI = PAR.CODBAI
        INNER JOIN sankhya.TSICID PCI
            ON PCI.CODCID = PAR.CODCID
        INNER JOIN sankhya.TSIUFS PUF
            ON PUF.CODUF = PCI.UF

            LEFT JOIN sankhya.TGFVEN VEN
            ON VEN.CODVEND = CAB.AD_CODVEND
        LEFT JOIN sankhya.TSIUSU TUS
            ON TUS.CODVEND = VEN.CODVEND
        WHERE OSE.NUMOS = OSE.NUMOS
        ";

        $service = new SankhyaDbExplorerSPService();
        $result = $service->fetchAll($sql);
        $result = $service->mapDbExplorerResult($result);

        $formatted = [];
        
        foreach ($result as $item) {
            $formatted[] = [
                'idOs' => $item['idOs'] ?? null,
                'numOs' => $item['numOs'] ?? null,
                'parceiro' => [
                    'codigo' => $item['CODPARC'] ?? null,
                    'razaoSocial' => $item['PrazaoSocial'] ?? null,
                    'nomeFantasia' => $item['PnomeFantasia'] ?? null,
                    'cpfCnpj' => $item['PcpfCnpj'] ?? null,
                    'logradouro' => $item['Plogradouro'] ?? null,
                    'numero' => $item['Pnumero'] ?? null,
                    'bairro' => $item['Pbairro'] ?? null,
                    'cidade' => $item['Pcidade'] ?? null,
                    'complemento' => $item['Pcomplemento'] ?? null,
                    'cep' => $item['Pcep'] ?? null,
                    'estado' => $item['Pestado'] ?? null,
                ],
                'certificado' => [
                    'validadeContrato' => $item['validadeContrato'] ?? null,
                    'licencaFuncionamento' => $item['licencaFuncionamento'] ?? null,
                    'dataValidadeLicenca' => $item['dataValidadeLicenca'] ?? null,
                    'dataInicioValidade' => $item['dataInicioValidadeCertificado'] ?? null,
                    'dataFimValidade' => $item['dataFimValidadeCertificado'] ?? null,
                ],
                'nikkey' => [
                    'razaoSocial' => $item['razaoSocial'] ?? null,
                    'nomeFantasia' => $item['nomeFantasia'] ?? null,
                    'cpfCnpj' => $item['cpfCnpj'] ?? null,
                    'logradouro' => $item['logradouro'] ?? null,
                    'numero' => $item['numero'] ?? null,
                    'bairro' => $item['bairro'] ?? null,
                    'cidade' => $item['cidade'] ?? null,
                    'complemento' => $item['complemento'] ?? null,
                    'cep' => $item['cep'] ?? null,
                    'estado' => $item['estado'] ?? null,
                    'telefone' => $item['telefone'] ?? null,
                ],
                'responsavelTecnico' => [
                    'razaoSocial' => $item['TrazaoSocial'] ?? null,
                    'nomeFantasia' => $item['TnomeFantasia'] ?? null,
                    'cpfCnpj' => $item['cpfCnpj'] ?? null,
                    'nomeConselho' => $item['nomeConselho'] ?? null,
                    'numeroConselho' => $item['numeroConselho'] ?? null,
                    'imagemAssinatura' => $this->hexToBase64($item['imagemAssinatura'] ?? null),
                ]
            ];
        }

        return response()->json($result ?? null);
    }
}
