require 'rubygems'
gem 'beanstalk-client'
require 'beanstalk-client'

beanstalk = Beanstalk::Pool.new(['localhost:11300'])
beanstalk.put('hello')

