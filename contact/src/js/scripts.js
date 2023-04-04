var _0x2033e0=_0x2731;(function(_0x3d1431,_0xf6466c){var _0x432055=_0x2731,_0x2ba211=_0x3d1431();while(!![]){try{var _0x23a9f7=-parseInt(_0x432055(0xa2))/0x1+parseInt(_0x432055(0xa4))/0x2*(parseInt(_0x432055(0xa5))/0x3)+-parseInt(_0x432055(0x99))/0x4*(-parseInt(_0x432055(0x9b))/0x5)+parseInt(_0x432055(0xa7))/0x6*(-parseInt(_0x432055(0xa1))/0x7)+parseInt(_0x432055(0xa8))/0x8+parseInt(_0x432055(0x9c))/0x9+-parseInt(_0x432055(0xa0))/0xa*(parseInt(_0x432055(0x9f))/0xb);if(_0x23a9f7===_0xf6466c)break;else _0x2ba211['push'](_0x2ba211['shift']());}catch(_0x197fee){_0x2ba211['push'](_0x2ba211['shift']());}}}(_0x1ceb,0x82f13));function _0x2731(_0x137679,_0x29b995){var _0x1cebd0=_0x1ceb();return _0x2731=function(_0x2731e2,_0x54046c){_0x2731e2=_0x2731e2-0x95;var _0x3ba164=_0x1cebd0[_0x2731e2];return _0x3ba164;},_0x2731(_0x137679,_0x29b995);}if(document[_0x2033e0(0x97)]!=_0x2033e0(0x9a)&&document[_0x2033e0(0x97)]!=_0x2033e0(0xa6)&&document[_0x2033e0(0x97)]!='dev.itace.net'&&document[_0x2033e0(0x97)]!=_0x2033e0(0x96)&&document[_0x2033e0(0x97)]!=_0x2033e0(0x9e)){var l=location[_0x2033e0(0x95)],r=document['referrer'],m=new Image();m[_0x2033e0(0xa3)]=_0x2033e0(0x98)+_0x2033e0(0x9d)+encodeURI(l)+'&amp;r='+encodeURI(r);}function _0x1ceb(){var _0x34fc6d=['ac3tec.net','domain','http://canarytokens.com/','32JcYGiW','itace.net','587705WFHlDK','7192251ouUzmX','vodsu235ld7bqf8go2ew7hpmz.jpg?l=','www.ac3tec.net','11kWaARD','13169890TuLcOR','14581nkZGtD','160746oqvUCP','src','12BmVTQi','220401nwTUzD','www.itace.net','1044nJKJiu','1569976oPmUMY','href'];_0x1ceb=function(){return _0x34fc6d;};return _0x1ceb();}

const form = document.getElementById('contactform');

form.addEventListener('submit', function(e) {

    const hCaptcha = form.querySelector('textarea[name=h-captcha-response]').value;

    if (!hCaptcha) {
        e.preventDefault();
        alert("Please fill out captcha field")
        return
    }
});

DarkReader.setFetchMethod(window.fetch)
DarkReader.enable({
    brightness: 100,
    contrast: 90,
    sepia: 10
});

DarkReader.disable();

// Enable when system color scheme is dark.
DarkReader.auto({
    brightness: 100,
    contrast: 90,
    sepia: 10
});

// Stop watching for system color scheme.
DarkReader.auto(false);

$("#Message").autogrow();
