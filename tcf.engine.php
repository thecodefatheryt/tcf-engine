<?
	##################################################
	# Template Motoru             					 # 
	# tcf.engine.php             					 # 
	# Mesut Cemil ASLAN / 2015 - 2023 / 			 # 
	# Versiyon Adı : Sektir 						 #
	# Versiyon Kodu : 2.0							 #
	# youtube.com/thecodefather						 #
	##################################################

    class Template
    {
        protected $file;
        protected $template;
        protected $values = array();
        
        public function __construct($file){
            $this->file = $file;
        }

        public function __call($method, $arguments) {
            if($method == 'set') {
                if(count($arguments) == 2) {
                   return call_user_func_array(array($this,'setSingle'), $arguments);
                }
                else if(count($arguments) == 1) {
                   return call_user_func_array(array($this,'setArray'), $arguments);
                }
            }
        } 

        private function setSingle($key,$value){
            if(is_array($value)){
                $this->values = array_merge(array($key => $value), $this->values);
            }else{
                $this->values[$key] = $value;
            }
        }

        private function setArray($values){
            if(!is_array($values))
            {
                die("Hatalı Set Kullanımı : Tekil Parametre Array olmalı !");
            }
            foreach($values as $key => $value){
                $this->set($key,$value);
            }
        }

        public function output($fixPath = true){
            if(!file_exists($this->file)) return "Şablon bulunamadı : ($this->file)";
            $this->template = file_get_contents($this->file);
            foreach($this->values as $key => $value){
                if(is_array($value)){
                    $this->template = $this->setArrayValue($this->template,$key,$value);
                }
                if(!is_array($value)){
                    $this->template = $this->setSingleValue($this->template,$key,$value);
                }
            }
            if($fixPath) $this->template = $this->fixPath();
            return $this->template;
        }

        private function fixPath(){
            $assetspath = dirname($this->file)."/assets/";
            return str_replace("assets/",$assetspath,$this->template);
        }

        private function setSingleValue($section,$key,$value){
            $toReplace = "{@$key}";
            return str_replace($toReplace,$value,$section);
        }

        private function setArrayValue($section,$key,$value){
            $top = trim(preg_replace('/^(.*)?{FOR:@'.$key.'}.*$/is',"$1",$section));
            if($top === $section) $top = NULL;
            $bottom = trim(preg_replace('#^.*?{END:@'.$key.'}(.*)$#is',"$1",$section));
            if($bottom === $section) $bottom = NULL;
            $looptext = trim(preg_replace('#.*{FOR:@'.$key.'}(.*?){END:@'.$key.'}.*#is',"$1",$section));
            if($looptext === $section) {return $looptext;}
            $output = NULL;
            foreach($value as $subkey=>$subvalue){
                if(is_array($subvalue)){
                    @$output.= $this->fillArrayValue($looptext,$subvalue);
                }
                if(!is_array($subvalue)){
                    @$output = $this->setSingleValue($looptext,$subkey,$subvalue);
                }
            }
            return $top.$output.$bottom;
        }

        private function fillArrayValue($section,$value){
            foreach($value as $key => $val){
                if(!is_array($val)){
                    $section = $this->setSingleValue($section,$key,$val);
                }
                if(is_array($val)){
                    $section = $this->setArrayValue($section,$key,$val);
                }
            }
            return $section;
        }
    }
?>
