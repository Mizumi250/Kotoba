import { spawn } from 'node:child_process'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
const __dirname = path.dirname(fileURLToPath(import.meta.url))
const FRONT = path.join(__dirname, 'frontend')
const BACK  = path.join(__dirname, 'backend')

function run(label, cmd, args, cwd, color, env = process.env) {
  const colors = { frontend:'\x1b[36m', backend:'\x1b[35m', queue:'\x1b[33m', reverb:'\x1b[34m' }
  const c = colors[color] || '', r = '\x1b[0m'
  const windowsScript = process.platform === 'win32' && (cmd === 'npm' || cmd === 'npx')
  const executable = windowsScript ? (process.env.ComSpec || 'cmd.exe') : cmd
  const commandArgs = windowsScript ? ['/d', '/s', '/c', cmd + '.cmd ' + args.map(arg => JSON.stringify(arg)).join(' ')] : args
  const p = spawn(executable, commandArgs, { cwd, stdio: 'pipe', env: { ...env, FORCE_COLOR: '1' } })
  const out = d => String(d).trim().split('\n').forEach(l => l && console.log(`${c}[${label}]${r} ${l}`))
  p.stdout?.on('data', out)
  p.stderr?.on('data', out)
  p.on('exit', code => code && code !== 0 && console.log(`${c}[${label}]${r} exited ${code}`))
  return p
}

console.log('\n  Starting kotoba...')
console.log('  Backend:  http://localhost:8001')
console.log('  Reverb:   ws://localhost:8081')
console.log('  Frontend: http://localhost:3001\n')

const backend  = run('backend',  'php', ['artisan', 'serve', '--port=8001'], BACK, 'backend')
const reverb   = run('reverb',   'php', ['artisan', 'reverb:start', '--host=0.0.0.0', '--port=8081', '--debug'], BACK, 'reverb')
const frontend = run('frontend', 'npm',  ['run', 'dev'], FRONT, 'frontend')

const services = [ backend, reverb, frontend].filter(Boolean)

process.on('SIGINT', () => {
  services.forEach(s => s && s.kill())
  process.exit(0)
})