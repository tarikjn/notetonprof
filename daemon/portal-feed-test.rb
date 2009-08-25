require 'rubygems'
require 'json'
gem 'beanstalk-client'
require 'beanstalk-client'

message = {:job => 'refresh-assignments', :args => 'for-object prof 1'}.to_json

beanstalk = Beanstalk::Pool.new(['localhost:11300'])
beanstalk.put(message)

