import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { useAuth } from '../lib/auth';
import Button from '../components/ui/Button';
import Input from '../components/ui/Input';

const Login = () => {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [isLoading, setIsLoading] = useState(false);
  const { register, handleSubmit, formState: { errors } } = useForm();

  const onSubmit = async (data) => {
    setIsLoading(true);
    try {
      await login(data.email, data.password);
      navigate('/dashboard');
    } catch (error) {
      console.error('Login error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="dashboard-grain grid min-h-dvh bg-[var(--color-paper-2)] lg:grid-cols-[minmax(20rem,0.72fr)_1fr]">
      <section className="relative hidden overflow-hidden bg-black p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div className="flex items-baseline gap-3">
          <span className="text-4xl font-black tracking-[-0.09em]">TUX</span>
          <span className="text-xs tracking-[0.28em] text-neutral-500">CMS</span>
        </div>
        <div>
          <p className="max-w-sm text-4xl font-medium leading-[1.04] tracking-[-0.055em]">
            The quiet place behind your website.
          </p>
          <p className="mt-6 max-w-xs text-sm leading-6 text-neutral-500">
            Edit, review, and publish without getting in the way of the work.
          </p>
        </div>
        <p className="text-xs text-neutral-600">TuxCMS / Private workspace</p>
      </section>

      <main className="flex min-h-dvh items-center px-6 py-12 sm:px-12 lg:px-20">
        <div className="w-full max-w-md">
          <div className="mb-12 flex items-baseline gap-2 lg:hidden">
            <span className="text-2xl font-black tracking-[-0.08em]">TUX</span>
            <span className="text-[10px] tracking-[0.24em] text-neutral-500">CMS</span>
          </div>

          <div className="mb-9">
            <p className="mb-3 text-sm text-neutral-500">Private workspace</p>
            <h1 className="text-4xl font-semibold tracking-[-0.055em] sm:text-5xl">Welcome back.</h1>
            <p className="mt-4 text-sm leading-6 text-neutral-600">Sign in to manage your website.</p>
          </div>

          {/* Form */}
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
            <Input
              label="Email"
              type="email"
              placeholder="name@example.com"
              autoComplete="email"
              error={errors.email?.message}
              {...register('email', {
                required: 'Email is required',
                pattern: {
                  value: /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i,
                  message: 'Invalid email address',
                },
              })}
            />

            <Input
              label="Password"
              type="password"
              placeholder="••••••••"
              autoComplete="current-password"
              error={errors.password?.message}
              {...register('password', {
                required: 'Password is required',
                minLength: {
                  value: 6,
                  message: 'Password must be at least 6 characters',
                },
              })}
            />

            <Button
              type="submit"
              variant="primary"
              className="mt-2 w-full py-3"
              isLoading={isLoading}
            >
              Sign in
            </Button>
          </form>

          {/* Accounts are created with `php artisan user:create` — there is
              no public registration on a single-site CMS. */}
          <p className="mt-7 text-sm text-neutral-500">
            Access is managed by your site administrator.
          </p>
        </div>
      </main>
    </div>
  );
};

export default Login;
