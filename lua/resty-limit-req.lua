--
-- Github: https://github.com/Tinywan
-- Blog: http://www.cnblogs.com/Tinywan
-- Author: Tinywan(SHaoBo Wan)
-- DateTime: 2017/9/22 17:31
-- Mail: Overcome.wan@Gmail.com
--
local limit_req = require "resty.limit.req"
local rate = 2 --固定平均速率2r/s
local burst = 10 --桶容量
local error_status = 503
local nodelay = false --是否需要不延迟处理

local lim, err = limit_req.new("limit_req_store", rate, burst)
if not lim then
    ngx.exit(error_status)
end

local key = ngx.var.binary_remote_addr --IP维度限流
--请求流入，如果你的请求需要被延迟则返回delay>0
local delay, err = lim:incoming(key, true)

if not delay then
    if err == "rejected" then
        ngx.log(ngx.ERR, "error : 503 ", err)
        return ngx.exit(503)
    end
    ngx.log(ngx.ERR, "failed to limit traffic: ", err)
    return ngx.exit(500)
end

if delay > 0 then --根据需要决定延迟或者不延迟处理
    if nodelay then
        --直接突发处理
    else
        ngx.sleep(delay) --延迟处理
    end
end

