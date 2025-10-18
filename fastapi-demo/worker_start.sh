#!/usr/bin/env bash
export PYTHONPATH=.
export REDIS_URL=${REDIS_URL:-redis://localhost:6379/0}
rq worker default
