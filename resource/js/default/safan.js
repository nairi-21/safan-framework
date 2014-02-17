var Safan = {

}
/// ------------------------------------- Core --------------------------------------------- ///

var SafanCore = {
    
    errorStatus : 404,
    completedStatus : 200,

    params: {},
    /**
     * example setParam('id', 5);
     */
    setParam: function(key, value){
        this.params[key] = value; 
    },
    /**
     * example setAjax('/users', 'post')
     */
    setAjax: function(url, type){
        //Method type
        if(type == undefined){
            type = "POST";
        }
        var data = this.params;
        var obj = JSON.stringify( data );
        var ajax = $.ajax({
            type: type,
            url: url,
            data: {data:obj},
            async: false,
        });
        this.params = {};
        if(ajax.status == this.completedStatus){
            return ajax.responseText;
        }
        return false;
    },
}









