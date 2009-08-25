#!/usr/bin/env ruby

require 'rubygems'
require 'json'
require 'logger'
require 'daemons'
gem 'beanstalk-client'
require 'beanstalk-client'

# TODO: set-up server to run on start-up (after beanstalkd), setup in capistrano

# TODO: concurrent execution issues?? -- locking? (keep message in queue, wait for finish)
# -> solution: use tubes
# specific tube for assignments
# TODO: auto-eliminate duplicate messages for assignments tube

# TODO: secure beanstalkd: only accept localhost connections

ALLOWED_PROGRAMS = ['refresh-assignments']
WD = Dir.pwd

Daemons.run_proc('portal-worker.rb') do
  # start of the daemon
  
  # for some reason, the working directory need to be restored
  Dir.chdir(WD)
  
  # trap Ctrl-C
  trap("INT") { puts "interrupted"; exit; }
  
  log = Logger.new('logs/portal-worker.log', 'daily')
  log.level = Logger::INFO

  beanstalk = Beanstalk::Pool.new(['localhost:11300'])


  loop do
    job = beanstalk.reserve
    
    #puts job.body # prints "hello"
    
    begin
    	message = JSON.parse(job.body)
    rescue
      log.error("can't parse JSON")
    end
    
    if message then
    	# execute job
      if ALLOWED_PROGRAMS.include?(message["job"])
        
        command = "../jobs/#{message["job"]}.php #{message["args"]}"
        result = `#{command}`
      
      end
      
      # log job result
      log.info(message["job"]) { "#{command} => #{result}" }
      
    end
    
    
    # execute job script into separate thread
    # TODO: when more than 1 task
    # specific for certain tubes
    
    job.delete
  end
  
  # end of the daemon
end
