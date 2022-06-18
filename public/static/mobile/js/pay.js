    
	//调用微信JS api 支付
	function jsApiCall(jsApiParameters,callback)
	{
        jsApiParameters=$.parseJSON(jsApiParameters);
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			jsApiParameters,
			function(res){
    			if(res.err_msg == 'get_brand_wcpay_request:ok'){
    			   alert("支付成功");
                   (callback && typeof(callback) === "function") && callback();  
    			}else{
    			   //alert(res.err_msg);
    			}
			}
		);
	}
	function pay(money,type,callback){
	     var get_pay_json={
            "type":type,
            "money":money,
            "order_no":make_order_no()
        }
	     ajax({
          type:'POST',
          url:"http://xingmuhy.zihaiwangluo.com/api/v1.0/pay/add",
          dataType:'json',
          data:get_pay_json,
          success:function(r){
              if(r["code"] == "0" ){
                var data=r["data"];
                if (typeof WeixinJSBridge == "undefined"){
          		    if( document.addEventListener ){
          		        document.addEventListener('WeixinJSBridgeReady', jsApiCall(data,callback), false);
          		    }else if (document.attachEvent){
          		        document.attachEvent('WeixinJSBridgeReady', jsApiCall(data,callback)); 
          		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall(data,callback));
          		    }
            		}else{
            		    jsApiCall(data,callback);
            		}
              }
          },
          error:function(jqXHR){
              //请求失败函数内容
          }
        }); 
	}

  function make_order_no(first) {
    if(!first){first="";}
    var order_no=first;
    order_no+=Math.floor(new Date().getTime()/1000);
    order_no+=Math.floor(Math.random()*3);
    return order_no;
  }