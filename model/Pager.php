<?php
	class bPack_Pager
	{
            protected $total = 0;
            protected $current = 0;            
            protected $per = 3;
                        
            public function total($total = 0)
            {
                $this->total = $total;
                
                return $this;
            }
            
            public function current($current = 1)
            {
                if($current > $this->total)
                {
                    $current = $this->total;
                }
                else
                {
                    $this->current = $current;
                }               
                
                return $this;
            }
            
            public function per($per = 0)
            {
                $this->per = $per;
                
                return $this;
            }
            
            public function getTotal()
            {
                return $this->total;
            }
            
            public function getCurrent()
            {
                return $this->current;
            }
            
            public function getPrevious($offset = 1)
            {
                return (($this->current - $offset) > 0) ? ($this->current - $offset) : false; 
            }
            
            public function getNext($offset = 1)
            {
                 return (($this->current + $offset) <= $this->getTotal()) ? ($this->current + $offset) : false;
            }
                        
            public function getPer()
            {
                return $this->per;
            }
            
            public function output (bP_Pager_Decorator $decorator, $base_URL = '')
            {
                return $decorator->process($this,$base_URL);
            }
            
            public function __toString()
            {
                return 'Pager: '.$this->getCurrent().' / '.$this->getTotal();
            }
	}
        
        class bP_Pager_Decorator_Pagi implements bP_Pager_Decorator
        {
            protected $pager_object;
            protected $html_result;
            protected $next_result;
            protected $prev_result;
            protected $base_URL;
                        
            public function Process($pager_object,$base_URL)
            {
                $this->pager_object = $pager_object;
                $this->base_URL = $base_URL;
                
                return $this->addPrev()->addNext()->addFirst()->addLast()->processSliding()->generateHTML();
            }
            
            public function addPrev()
            {
                if($this->pager_object->getTotal() > 1)
                {
                    if($this->pager_object->getCurrent() >  1)
                    {
                         $this->prev_result = $this->putLink('<img border="0" src="'.bPack_BaseDir.'tpl/Pager/prev.png" width="16" height="16">',$this->pager_object->getPrevious()) ;
                    }
                }
                
                return $this;
            }
            
            public function addFirst()
            {
                if($this->pager_object->getTotal() > 1)
                {
                    if($this->pager_object->getCurrent() >  1)
                    {
                         $this->prev_result = $this->putLink('<img border="0" src="'.bPack_BaseDir.'tpl/Pager/first.png" width="16" height="16">',1) .  '&nbsp;' .$this->prev_result ;
                    }
                }
                
                return $this;
            }
            
            public function addLast()
            {
                if($this->pager_object->getTotal() > 1)
                {
                    if($this->pager_object->getCurrent() < $this->pager_object->getTotal())
                    {
                         $this->next_result .= '&nbsp;' . $this->putLink('<img border="0" src="'.bPack_BaseDir.'tpl/Pager/last.png" width="16" height="16">',$this->pager_object->getTotal())  ;
                    }
                }
                
                return $this;
            }
            
            public function addNext()
            {
                if($this->pager_object->getTotal() > 1)
                {
                    if($this->pager_object->getCurrent() < $this->pager_object->getTotal())
                    {
                        $this->next_result = $this->putLink('<img border="0" src="'.bPack_BaseDir.'tpl/Pager/next.png" width="16" height="16">',$this->pager_object->getNext());
                    }
                }
                
                return $this;
            }
            
            protected function putLink($string,$page = 0)
            {
                return '<a href="'.$this->base_URL.((strpos($this->base_URL,'?')) ? '&' : '?').'page='.$page.'" style="text-decoration:none; color:#666666; font-size:12px;">'.$string.'</a>';
            }
            
            public function processSliding()
            {
                if($this->pager_object->getTotal() > 1)
                {
                    $per = $this->pager_object->getPer();
                    $leftandright = floor(($per - 1) / 2);
                    
                    $leftLinks = array();
                    $rightLinks = array();
                    
                    for($i=1;$i<=$leftandright;$i++)
                    {
                        if(($prev = $this->pager_object->getPrevious($i)) !== FALSE) $leftLinks[$leftandright-$i] = $this->putLink($prev,$prev);
                        if(($next = $this->pager_object->getNext($i)) !== FALSE) $rightLinks[$i] = $this->putLink($next,$next);
                    }

                    natsort($leftLinks);
                    natsort($rightLinks);
                    
                    $this->html_result = implode('&nbsp;',$leftLinks) . '&nbsp;<b style="font-size:13px;">'.$this->pager_object->getCurrent().'</b>&nbsp;' . implode('&nbsp;',$rightLinks);
                }
                else
                {
                     $this->html_result = '<span style="font-family:Tahoma;font-size: 12px;color:#666666;">第 <b  style="font-size:12px;">1</b> 頁</span>';
                }
                
                return $this;
            }
            
            public function generateHTML()
            {
                return '<!-- Pager START -->' . $this->prev_result . '&nbsp;'. $this->html_result . '&nbsp;'. $this->next_result .  '<!-- Pager END -->' ;
            }
        }
        
        interface bP_Pager_Decorator
        {            
            public function Process($pager_object,$base_URL);
            public function addPrev();
            public function addNext();
            public function generateHTML();
        }
