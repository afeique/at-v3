function drawFrontpageClock() {
    var canvas = document.getElementById('frontpage-clock-canvas');
    if (canvas.getContext) {
        var context = canvas.getContext('2d');
        context.clearRect(0, 0, 300, 300);

        // gradients, fonts, setup
        var innerStrokeGradient = context.createRadialGradient(150, 75, 30, 150, 75, 150);
        var outerStrokeGradient = context.createRadialGradient(150, 75, 50, 150, 75, 150);
        innerStrokeGradient.addColorStop(0, '#3d3d3d');
        innerStrokeGradient.addColorStop(1, '#aaa');
        outerStrokeGradient.addColorStop(0, '#3d3d3d');
        outerStrokeGradient.addColorStop(1, '#000');
        context.font = 'Bold 20px Arial';
        context.textBaseline = 'middle';
        context.textAlign = 'center';

        // common stroke properties of both bezels
        context.lineWidth = 3;
        context.shadowOffsetX = 2;
        context.shadowOffsetY = 2;
        context.shadowBlur = 3;
        context.shadowColor = 'rgba(0,0,0, .3)';

        // inner stroke of outer bezel
        context.strokeStyle = innerStrokeGradient;
        context.lineWidth = 3;
        context.beginPath();
        context.arc(150, 75, 57, 0, Math.PI*2, true);
        context.stroke();

        // outer stroke of outer bezel
        context.strokeStyle = outerStrokeGradient;
        context.beginPath();
        context.arc(150, 75, 60, 0, Math.PI*2, true);
        context.stroke();

        // draw the different hands
        var drawHand = function(length, width, percentOffset, rot) {
            // save existing draw context onto the stack
            context.save();

            // rotate the whole hand as necessary
            context.translate(canvas.width / 2, canvas.height / 2);
            context.rotate(rot);

            /**
             * We're going to draw an elongated triangular path (read: incredibly obtuse triangle),
             * fill it, and transform the triangle (mirror it about the x-axis) to obtain our filled
             * path. To finish, we'll add some shadow and stroke it. (yeah, baby)
             */

            // make some calculations... calculate the triangle's leftmost angle
            var height = width / 2;
            var offset = (percentOffset/100) * length;
            
            var tanAlpha = height / offset;
            var alpha = Math.atan(tanAlpha);

            // create the path to fill
            context.beginPath();

            // draw half the hand, just a triangle
            var drawHalf = function() {
                context.moveTo(0,0);
                context.lineTo(offset, Math.sin(alpha) * height);
                context.lineTo(length, 0);
                context.lineTo(0,0);
                context.closePath();
            }

            // draw half the hand, transform it, then draw the other half
            drawHalf();
            context.scale(1,-1);
            drawHalf();

            var gradient = context.createLinearGradient(0,0,length,height);
            gradient.addColorStop(0,'#4d4d4d');
            gradient.addColorStop(1,'#1d1d1d');
            context.fillStyle = gradient;
            context.fill();

            // setup stroke and shadow

            // add shadow
            context.shadowBlur = 1;
            context.shadowOffsetX = 1;
            context.shadowOffsetY = 1;
            context.shadowColor = 'rgba(0,0,0, .3)';

            context.restore();
        }

        // current time
        var now = new Date();
        drawHand(50, 10, 12, Math.PI/30*now.getSeconds());

        setTimeout(drawFrontpageClock, 1000);
    }
}

drawFrontpageClock();
        /*
        //Outer bezel
        context.strokeStyle=grad1;
        context.lineWidth=10;
        context.beginPath();
        context.arc(150,150,138,0,Math.PI*2,true);
        context.shadowOffsetX=4;
        context.shadowOffsetY=4;
        context.shadowColor="rgba(0,0,0,0.6)";
        context.shadowBlur=6;
        context.stroke();
        //Inner bezel
        context.restore();
        context.strokeStyle=grad2;
        context.lineWidth=10;
        context.beginPath();
        context.arc(150,150,129,0,Math.PI*2,true);
        context.stroke();
        context.strokeStyle="#222";
        context.save();
        context.translate(150,150);
        //Markings/Numerals
        for (i=1;i<=60;i++) {
          ang=Math.PI/30*i;
          sang=Math.sin(ang);
          cang=Math.cos(ang);
          //If modulus of divide by 5 is zero then draw an hour marker/numeral
          if (i % 5 == 0) {
            context.lineWidth=8;
            sx=sang*95;
            sy=cang*-95;
            ex=sang*120;
            ey=cang*-120;
            nx=sang*80;
            ny=cang*-80;
            context.fillText(i/5,nx,ny);
          //Else this is a minute marker
          } else {
            context.lineWidth=2;
            sx=sang*110;
            sy=cang*110;
            ex=sang*120;
            ey=cang*120;
          }
          context.beginPath();
          context.moveTo(sx,sy);
          context.lineTo(ex,ey);
          context.stroke();
        }
        //Fetch the current time
        var ampm="AM";
        var now=new Date();
        var hrs=now.getHours();
        var min=now.getMinutes();
        var sec=now.getSeconds();
        context.strokeStyle="#000";
        //Draw AM/PM indicator
        if (hrs>=12) ampm="PM";
        context.lineWidth=1;
        context.strokeRect(21,-14,44,27);
        context.fillText(ampm,43,0);
        
        context.lineWidth=6;
        context.save();
        //Draw clock pointers but this time rotate the canvas rather than
        //calculate x/y start/end positions.
        //
        //Draw hour hand
        context.rotate(Math.PI/6*(hrs+(min/60)+(sec/3600)));
        context.beginPath();
        context.moveTo(0,10);
        context.lineTo(0,-60);
        context.stroke();
        context.restore();
        context.save();
        //Draw minute hand
        context.rotate(Math.PI/30*(min+(sec/60)));
        context.beginPath();
        context.moveTo(0,20);
        context.lineTo(0,-110);
        context.stroke();
        context.restore();
        context.save();
        //Draw second hand
        context.rotate(Math.PI/30*sec);
        context.strokeStyle="#E33";
        context.beginPath();
        context.moveTo(0,20);
        context.lineTo(0,-110);
        context.stroke();
        context.restore();
        
        //Additional restore to go back to state before translate
        //Alternative would be to simply reverse the original translate
        context.restore();
        setTimeout(draw,1000);
      }
    }
}*/