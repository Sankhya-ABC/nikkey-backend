<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\Sankhya\AuthSankhya;
use App\Services\Sankhya\SankhyaLoadRecordsService;
use App\Services\Sankhya\SankhyaDbExplorerSPService;

class DashboardAdminController extends Controller {
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

        return response()->json($result[0] ?? null);
        return $result;
    }
}
