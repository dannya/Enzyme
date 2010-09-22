function setCurrentItem(id){if(!$(id)||!$(id+'-type')||!$(id+'-area')){return null;}
if(!$(id+'-type').value.empty()&&!$(id+'-area').value.empty()){$(id).className='item marked';$(id).down('div.commit-classify').className='commit-classify filled';}else{$(id).className='item selected';$(id).down('div.commit-classify').className='commit-classify unfilled';}
updateCounter();}
function updateCounter(){commitCounter=0;$$('div.item').each(function(item){if($(item.id+'-type')&&!$(item.id+'-type').value.empty()&&$(item.id+'-area')&&!$(item.id+'-area').value.empty()){commitCounter++;}});$('commit-counter').update(commitCounter);}
function changeKey(theType){if(typeof theType=='undefined'||!$('classify-key-areas')||!$('classify-key-types')){return false;}
if(theType=='areas'){var element1=$('classify-key-types');var element2=$('classify-key-areas');}else if(theType=='types'){var element1=$('classify-key-areas');var element2=$('classify-key-types');}
if($('classify-key-'+theType).visible()){new Effect.Fade($('classify-key-'+theType),{duration:0.3});}else{element1.hide();new Effect.BlindDown(element2,{duration:0.3});}
$$('input.classify-key-button').each(function(button){button.removeClassName('selected');});$('classify-key-button-'+theType).addClassName('selected');}
document.observe("dom:loaded",function(){if($("commit-total")){$("commit-total").update($$("div.item").size());}});