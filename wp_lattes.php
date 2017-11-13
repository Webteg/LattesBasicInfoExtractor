/*
 Inserir no functions.php:
    include('wp_lattes.php');
 Depois em qualquer página:
    [lattes]
*/
<?php
    function getPeople(){
        $txt_url="http://docs.google.com/document/export?format=txt&id=12uCOqdhoLR6QO-1ed6bE3epM-UW87YVCjpq2bYrgQ6s";
        $website = file_get_contents($txt_url);
        $people = explode("\r\n", $website);
        if (function_exists('wp_cache_set')){
            wp_cache_set( 'vitaes', $people, 'default', 1); //Em produção, mudar para 600 pelo menos
        }
        return $people;
    }
    
    
    function getVitaes() {
        $vitaes = false;
        if (function_exists('wp_cache_get')){
            $vitaes = wp_cache_get('vitaes');
            if (false !== $vitaes){
                echo $vitaes;
                return;
            }
        }
        
        $foto = 'http://servicosweb.cnpq.br/wspessoa/servletrecuperafoto?tipo=1&id=';
        
        $people = getPeople();
        
        $vitaes = '<table class="professores" border="0" cellspacing="2" cellpadding="1">';
        for($i = 0; $i < count($people); ++$i) {
            $vitaes=$vitaes.'<tr>'."\r\n";
            
            if (trim($people[$i])===''){
                continue;
            }
            
            $data = explode(",", $people[$i]);
            //var_dump($data);
            
            $url = get_redirect_target(get_redirect_target('http://buscatextual.cnpq.br/buscatextual/cv?id='.$data[1]));
            $kid = substr($url, strpos($url, 'id=')+3);
            
            $professor = ($data[0][0] !== ' ');
            
            if ($professor){
                $vitaes=$vitaes.'<td class="foto"><div class="fotodiv" style="background-image: url('."'".$foto.$kid."'".');"></div></td>'."\r\n";
                $vitaes=$vitaes.'<td class="nome" colspan="2"><a href="http://lattes.cnpq.br/'.$data[1].'">'.trim($data[0]).'</a></td>'."\r\n";
            }else{
                $vitaes=$vitaes.'<td></td>';
                $vitaes=$vitaes.'<td class="foto-aluno"><div class="foto-alunodiv" style="background-image: url('."'".$foto.$kid."'".');"></div></td>'."\r\n";
                $vitaes=$vitaes.'<td class="nome-aluno"><a href="http://lattes.cnpq.br/'.$data[1].'">'.trim($data[0]).'</a> <span style="font-size: 30%">('.$data[2].')</span></td>'."\r\n";
            }
            
            $vitaes=$vitaes.'</tr>'."\r\n";
        }
        $vitaes=$vitaes.'</table>';
        
        if (function_exists('wp_cache_set')){
            wp_cache_set( 'vitaes', $vitaes, 'default', 1); //Mudar para 86400
        }
        echo $vitaes;
    }


    
    /* Author: davejamesmiller */
    /* From: https://gist.github.com/davejamesmiller/dbefa0ff167cc5c08d6d */
    function get_redirect_target($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = curl_exec($ch);
        curl_close($ch);
        // Check if there's a Location: header (redirect)
        if (preg_match('/^Location: (.+)$/im', $headers, $matches))
            return trim($matches[1]);
        // If not, there was no redirect so return the original URL
        // (Alternatively change this to return false)
        return $url;
    }

    if (function_exists('add_shortcode')){
        add_shortcode('lattes', 'getVitaes');
    }
?>
