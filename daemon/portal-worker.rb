require 'rubygems'
gem 'beanstalk-client'
require 'beanstalk-client'

# TODO: demonize
# TODO: set-up server to run on start-up (after beanstalkd)

# TODO: concurrent execution issues?? -- locking? (keep message in queue, wait for finish)

# TODO: secure beanstalkd: only accept localhost connection

ALLOWED_PROGRAMS = ['refresh-assignements']

beanstalk = Beanstalk::Pool.new(['localhost:11300'])
loop do
  job = beanstalk.reserve
  
  puts job.body # prints "hello"
  
  # execute job script into separate thread
  
  job.delete
end
